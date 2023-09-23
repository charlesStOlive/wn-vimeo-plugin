<?php

namespace Waka\Vimeo\Models;

use Waka\Wformwidgets\Models\ApiFile;
use Illuminate\Support\Facades\Cache;


/**
 * File attachment model
 *
 */


class VimeoVideo extends ApiFile
{

    public $apiSrc = 'vimeo';

    public $metaToKeep = ['uri', 'name', 'description', 'embed.html', 'pictures', 'status'];


    private function getVimeo()
    {
        return new \Waka\Vimeo\Classes\VimeoWrapper();
    }

    public function sendToApi()
    {
        //trace_log('sendToApi');
        if ($this->api_id) {
            return;
        }
        //trace_log($this->toArray());
        try {
            $apiResult = $this->getVimeo()->uploadVideo($this->getLocalPath(), ['title' => $this->title, 'description' => $this->description]);
            $metas = $apiResult['body'];
            $this->api_id = $metas['uri'];
            $this->api_state = $metas['status'];
            $this->api_metas = $this->filterMeta($metas);
        } catch (\Exception $ex) {
            //trace_log($ex->getMessage());
        }
    }

    public function updateApi()
    {
        $apiOpts = [
            'title' => $this->title,
            'description' => $this->description,
            'subtitles' => $this->api_opts['subtitles'],
        ];
        try {
            $apiResult = $this->getVimeo()->updateVideo($this->api_id, $apiOpts);
            $metas = $apiResult['body'];
            $this->api_state = $metas['status'];
            $this->api_metas = $this->filterMeta($metas);
        } catch (\Exception $ex) {
            //trace_log($ex);
        }
    }

    public function getIframe($width, $height)
    {
        $iframeHtml = $this->api_metas['embed']['html'] ?? null;
        if (!$iframeHtml) {
            return null;
        } else if ($width != null && $height != null) {
            $iframeHtml = preg_replace('/width="\d+"/i', 'width="' . $width . '"', $iframeHtml);
            $iframeHtml = preg_replace('/height="\d+"/i', 'height="' . $height . '"', $iframeHtml);

            // Extract the src attribute
            preg_match('/src="([^"]*)"/i', $iframeHtml, $match);
            $src = $match[1] ?? '';

            // Add the texttrack parameter
            $newSrc = $src . (strpos($src, '?') !== false ? '&' : '?') . 'texttrack=fr';

            // Replace the old src with the new one
            $iframeHtml = str_replace($src, $newSrc, $iframeHtml);

            return $iframeHtml;
        } else {
            return $iframeHtml;
        }
    }


    public function isApiRessourceReady()
    {
        //trace_log('isApiRessourceReady');
        return Cache::remember('isRessourceReady' . $this->id, 2, function () {
            if (!$this->api_state) {
                return 'not_ready';
            }
            if ($this->api_state == 'available') {
                return 'ready';
            } elseif ($this->api_state == 'transcoding' || $this->api_state == 'uploading') {
                $ApiVideoInfo = $this->getVimeo()->getVideo($this->api_id);
                $this->api_state = $ApiVideoInfo['status'];
                if ($this->api_state == 'available') {
                    $this->api_metas = $this->filterMeta($ApiVideoInfo);
                    //trace_log($this->toArray());
                    $this->save();
                    return 'ready';
                } else {
                    return 'not_ready';
                }
            } else {
                return 'error_ready';
            }
        });
    }


    public function getThumb($width, $height, $options = [])
    {
        //trace_log('getThumb---------------');
        $pictures = $this->api_metas['pictures']['sizes'] ?? null;
        if (!$pictures) {
            return null;
        }
        $choosePicture = null;
        foreach ($pictures as $picture) {
            if ($picture['width'] > $width) {
                $choosePicture = $picture;
                break;
            }
        }
        if ($options['simple_link'] ?? false) {
            return $choosePicture['link'] ?? null;
        } else {
            return $choosePicture['link_with_play_button'] ?? null;
        }
    }
}
