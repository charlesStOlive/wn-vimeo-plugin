<div class="wakaupload-config-form">
    <?= Form::open(['data-request-parent' => "#{$parentElementId}"]) ?>
    <input type="hidden" name="file_id" value="<?= $file->id ?>" />
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="popup">&times;</button>
        <h4 class="modal-title">
            <!-- Modal header title goes here -->
            Visualiser et modifier la vidéo VIMEO
        </h4>
    </div>
    <div class="modal-body">
        <div style="display:flex">
            <div style="width:600px">
                <?php if ($file->isApiRessourceReady() == "ready") : ?>
                    <?= $file->getIframe(600, 305);  ?>
                <?php else : ?>
                    <img src="<?= $file->getThumb(600, 300) ?>" class="img-responsive center-block" alt="" title="<?= e(trans('backend::lang.wakaupload.attachment')) ?>: <?= e($file->file_name) ?>">
                <?php endif ?>
            </div>
            <div style="flex: 1 1 0%;">
                <?= $this->getConfigFormWidget()->render(['section' => 'outside']); ?>
            </div>
        </div>
        <?= $this->getConfigFormWidget()->render(['section' => 'secondary']); ?>

    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-primary" data-request="<?= $this->getEventHandler('onSaveAttachmentConfig') ?>" data-popup-load-indicator>
            <?= e(trans('backend::lang.form.save')) ?>
        </button>
        <button type="button" class="btn btn-default" data-dismiss="popup">
            <?= e(trans('backend::lang.form.cancel')) ?>
        </button>
    </div>
    <?= Form::close() ?>
</div>