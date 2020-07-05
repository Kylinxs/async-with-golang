
{if $type eq 'addreference' && !empty($prefs.feature_library_references) && $prefs.feature_library_references eq 'y'}
    {* if click new button, user is able to add new reference in current page. *}
    <div><a class="btn btn-secondary" id="plugin_addreference_button">Add Reference</a></div>
    <div id="add_reference_block" style="display:none;">
        <div>
            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label" for="add_ref_auto_biblio_code">{tr}Auto generate Biblio Code:{/tr}</label>
                <div class="col-sm-10">
                    <input type="checkbox" class="form-check wikiedit" name="ref_auto_biblio_code" id="add_ref_auto_biblio_code" checked="checked" />
                </div>
            </div>
            <div class="mb-3 row" id="add_biblio_form" style="display: none;">
                <label class="col-sm-2 col-form-label" for="add_ref_biblio_code">{tr}Biblio Code:{/tr}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control wikiedit" name="ref_biblio_code" id="add_ref_biblio_code" value="" />
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label" for="add_ref_author">{tr}Author:{/tr}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control wikiedit" name="add_ref_author" id="add_ref_author" value="" />
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label" for="add_ref_title">{tr}Title:{/tr}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control wikiedit" name="add_ref_title" id="add_ref_title" value="" />
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label" for="add_ref_year">{tr}Year:{/tr}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control wikiedit" name="add_ref_year" id="add_ref_year" value="" />
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label" for="add_ref_part">{tr}Part:{/tr}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control wikiedit" name="add_ref_part" id="add_ref_part" value="" />
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label" for="add_ref_uri">{tr}URI:{/tr}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control wikiedit" name="add_ref_uri" id="add_ref_uri" value="" />
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label" for="add_ref_biblio_code">{tr}Code:{/tr}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control wikiedit" name="add_ref_code" id="add_ref_code" value="" />
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label" for="add_ref_publisher">{tr}Publisher:{/tr}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control wikiedit" name="add_ref_publisher" id="add_ref_publisher" value="" />
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label" for="add_ref_location">{tr}Location:{/tr}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control wikiedit" name="add_ref_location" id="add_ref_location" value="" />
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label" for="add_ref_style">{tr}Style:{/tr}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control wikiedit" name="add_ref_style" id="add_ref_style" value="" />
                    <span class="form-text">{tr}Enter the CSS class name to be added in the 'li' tag for listing this reference.{/tr}</span>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label" for="add_ref_template">{tr}Template:{/tr}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control wikiedit" name="add_ref_template" id="add_ref_template" value="" />
                    <span class="form-text">
                                            {tr}Enter template format in which you want to display the reference details in the bibliography listing. For example:{/tr} ~title~ (~year~) ~author~
                                        </span>
                    <span class="form-text">
                                            {tr}All the codes must be in lower case letters separated with spaces.{/tr}
                                        </span>
                </div>
            </div>
            <div class="mb-3 row">
                <div class="offset-sm-2 col-sm-10">
                    <a class="wikiaction btn btn-primary" title="{tr}Add{/tr}" id="add_reference_submit">{tr}Add{/tr}</a>
                    <a class="wikiaction btn btn-warning" title="{tr}Cancel{/tr}" onclick="$('#add_reference_block').css('display','none'); return false;">{tr}Cancel{/tr}</a>
                    <span id="a_status" style="margin: 0 0 0 10px;"></span>
                </div>
            </div>
        </div>
    </div>
    <script type="application/javascript">
        $('#plugin_addreference_button').click(function(){
            var block = $('#add_reference_block');
            if (!block.is(':visible')){
                block.find('input').val('');
            }
            block.toggle();
        });
        $('#add_ref_auto_biblio_code').click(function(){
            if ($('#add_ref_auto_biblio_code').is(':checked')) {
                $('#add_biblio_form').hide();
            } else {
                $('#add_biblio_form').show();
            }
        });
        $('#add_reference_submit').click(function(e){
            e.preventDefault();

            var ticket = "{ticket mode='get'}";
            var ck_code = /^[A-Za-z0-9]+$/;
            {* var ck_uri = /^((https?|ftp|smtp):\/\/)?(www.)?[a-z0-9]+(\.[a-z]{2, }){1, 3}(#?\/?[a-zA-Z0-9#]+)*\/?(\?[a-zA-Z0-9-_]+=[a-zA-Z0-9-%]+&?)?$/; *}
            var ck_year = /^[1-2][0-9][0-9][0-9]$/;
            if (!$('#add_ref_auto_biblio_code').is(':checked') && $('#add_ref_biblio_code').val() == '') {
                alert('Please fill the biblio code field or enable biblio code auto generator');
                return false;
            }
            if(!$('#add_ref_auto_biblio_code').is(':checked') && !ck_code.test($('#add_ref_biblio_code').val())){
                alert('Biblio code is not valid');
                return false;
            }
            {* if(!$('#add_ref_uri').val() == '' &&  !ck_uri.test($('#add_ref_uri').val())){
                alert('uri no valid');
                return false;
            } *}
            if(!$('#add_ref_author').val().trim()){
                alert('Author is not valid');
                return false;
            }
            if(!$('#add_ref_year').val() == '' && !ck_year.test($('#add_ref_year').val())){
                alert('Year is not valid');
                return false;
            }

            var data = {
                ref_auto_biblio_code: $('#add_ref_auto_biblio_code').is(':checked') ? 'on' : 'off',
                ref_biblio_code: $('#add_ref_auto_biblio_code').is(':checked') ? '' : $('#add_ref_biblio_code').val(),
                ref_author: $('#add_ref_author').val(),
                ref_title: $('#add_ref_title').val(),
                ref_part: $('#add_ref_part').val(),
                ref_uri: $('#add_ref_uri').val(),
                ref_code: $('#add_ref_code').val(),
                ref_publisher: $('#add_ref_publisher').val(),
                ref_location: $('#add_ref_location').val(),
                ref_year: $('#add_ref_year').val(),
                ref_style: $('#add_ref_style').val(),
                ref_template: $('#add_ref_template').val(),
            };
            $.post('tiki-references.php?addreference=1&response=json' + ticket, data, null, 'json')
                .done(function(result){
                    if(result.success) {
                        if ($('#param_biblio_code_input').val() == '') {
                            $('#param_biblio_code_input').val(result.biblio_code);
                        }
                        var pageName = '{$pageName|escape}';
                        if (!pageName) {
                            pageName = $("#editpageform input[name='page']").val();
                        }
                        if (!!pageName && !!result.id) {
                            $.get('references.php?page=' + pageName + '&action=u_lib&ref_id=' + result.id + ticket);
                        }
                        alert('{tr}New reference created{/tr}');
                        $('#add_reference_block').hide();
                    } else {
                        alert('{tr}Problems while creating the reference:{/tr}' + "\n" + result.msg);
                    }
                }).fail(function(){
                alert('{tr}Problems while creating the reference{/tr}');
            });
            return false;
        });
    </script>
{/if}