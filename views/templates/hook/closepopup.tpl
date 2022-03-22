
<div id="closepopup" class="modal fade" data-backdrop="static" data-keyboard="false" role="dialog" data-custom-interval="{$CLOSEPOPUP_TIME_INTERVAL}">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="color: {$CLOSEPOPUP_TEXT_COLOR}; background-color: {$CLOSEPOPUP_BG_COLOR}; {if $CLOSEPOPUP_BG_IMAGE}background-image: url('{$CLOSEPOPUP_IMG_DIR}{$CLOSEPOPUP_BG_IMAGE}'){/if}">
            <div class="modal-header">
                <i class="icon-hand_stop_icon"></i>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                {$CLOSEPOPUP_CONTENT nofilter}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="color: {$CLOSEPOPUP_BG_COLOR}">{l s='Return to site'} <i class="icon-arrow_right_icon"></i></button>
            </div>
        </div>
    </div>
</div>