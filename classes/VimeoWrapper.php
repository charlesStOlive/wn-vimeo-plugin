<?php

namespace Waka\Vimeo\Classes;

use Vimeo\Vimeo;
use Config;
use Storage;

class VimeoWrapper
{
    protected $client;

    public function __construct()
    {
        $client_id = Config::get('vimeo.credentials.client_id');
        $client_secret = Config::get('vimeo.credentials.client_secret');
        $access_token = Config::get('vimeo.credentials.access_token');

        $this->client = new Vimeo($client_id, $client_secret, $access_token);
    }

    public function uploadVideo($file_path, $options)
    {
        $uri = $this->client->upload($file_path, [
            'name' => $options['title'] ?? 'Your video title',
            'description' => $options['description'] ?? 'Your video description',
        ]);

        return $this->client->request($uri);
    }

    public function getVideo($video_id)
    {
        $response = $this->client->request($video_id);

        if ($response['status'] !== 200) {
            throw new \Exception("Failed to get video: " . $response['body']['error']);
        }

        return $response['body'];
    }

    public function getUploadQuota()
    {
        $response = $this->client->request('/me');

        if ($response['status'] !== 200) {
            throw new \Exception("Failed to get upload quota: " . $response['body']['error']);
        }

        return $response['body']['upload_quota']['space']['free'];
    }

    public function updateVideo($video_id, $options)
    {
        //trace_log($options);
        $params = [];

        if (isset($options['title'])) {
            $params['name'] = $options['title'];
        }

        if (isset($options['description'])) {
            $params['description'] = $options['description'];
        }

        if (isset($options['picture_uri'])) {
            $params['pictures'] = [
                'active' => true,
                'uri' => $options['picture_uri']
            ];
        }

        $response = $this->client->request($video_id, $params, 'PATCH');

        if ($response['status'] !== 200) {
            throw new \Exception("Failed to update video: " . $response['body']['error']);
        }

        // trace_log('ok');

        // If there are subtitles, update them
        if (!empty($options['subtitles'])) {
            $this->updateSubtitle($video_id, $options['subtitles']);
        }

        return $response['body'];
    }

    public function updateSubtitle($video_id, $subtitles)
    {
        // Obtain an instance of the storage
        $storage = Storage::disk('local');

        // Generate a unique filename for the VTT file
        $tempFilename = uniqid('vtt', true) . '.vtt';

        // Initialize the VTT data
        $vttData = "WEBVTT\n\n";

        // Write each subtitle to the VTT data
        foreach ($subtitles as $index => $subtitle) {
            // Convert the times to the correct format
            $start = $this->convertTimeToVTT($subtitle['start']);
            $end = $this->convertTimeToVTT($subtitle['end']);

            // Add the subtitle index, time, and text to the VTT data
            $vttData .= ($index + 1) . "\n";
            $vttData .= $start . " --> " . $end . "\n";
            $vttData .= $subtitle['text'] . "\n\n";
        }

        // Store the VTT data to a file
        $storage->put($tempFilename, $vttData);

        // Get the path of the file
        $tempFilePath = $storage->path($tempFilename);

        // Delete existing text tracks (subtitles)
        $response = $this->client->request($video_id . '/texttracks', [], 'DELETE');
        //trace_log($response);
        // Upload the new subtitles
        $response = $this->client->uploadTextTrack($video_id . '/texttracks', $tempFilePath, 'captions', 'fr');
        //trace_log($response);

        // Delete the temporary file
        $storage->delete($tempFilename);

        return $response;
    }




    // Helper function to convert time to the correct format for VTT
    private function convertTimeToVTT($time)
    {
        list($minutes, $seconds) = explode(':', $time);
        return "00:" . str_pad($minutes, 2, '0', STR_PAD_LEFT) . ":" . str_pad($seconds, 2, '0', STR_PAD_LEFT) . ".000";
    }

    public function setThumbnail($video_id, $thumbnail_index)
    {
        $response = $this->client->request($video_id . '/pictures', [], 'POST');

        if ($response['status'] !== 201) {
            throw new \Exception("Failed to get pictures: " . $response['body']['error']);
        }

        $pictures = $response['body']['data'];

        if (!isset($pictures[$thumbnail_index])) {
            throw new \Exception("Invalid thumbnail index: $thumbnail_index");
        }

        $picture_uri = $pictures[$thumbnail_index]['uri'];

        $response = $this->client->request($video_id . '/pictures', ['uri' => $picture_uri], 'PATCH');

        if ($response['status'] !== 200) {
            throw new \Exception("Failed to set thumbnail: " . $response['body']['error']);
        }

        return $response['body'];
    }
}
