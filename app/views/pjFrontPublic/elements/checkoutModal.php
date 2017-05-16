<div class="modal fade pjBsModal pjBsModalTerms" id="pjBsModalTerms" tabindex="-1" role="dialog" aria-labelledby="pjBsModalTermsLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <header class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

                <p class="modal-title"><? __('front_label_terms_conditions');?></p><!-- /.modal-title -->
            </header><!-- /.modal-header -->

            <div class="modal-body">
                <?=nl2br(pjSanitize::clean($tpl['terms_conditions']));?>
            </div><!-- /.modal-body -->
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /#pjBsModalTerms.modal fade pjBsModal pjBsModalTerms -->