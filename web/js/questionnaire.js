'use strict';

var questionnaire = {

    // Appel global
    init: function()
    {
        this.init_slider();
        //this.linkedQuestions(toggle);
        this.submit_questionnaire();
    },

    // -----------------------------------------------------------------------------------------------------------------

    //Initialise les input de class Slider
    init_slider: function(){
        $('.sliderform').each(function()
        {
            var minimum = parseInt($(this).attr('init-min'));
            var maximum = parseInt($(this).attr('init-max'));
            var select = $(this);
            var slider = $("<div class='slider'></div>").insertAfter( select ).slider({
                min: minimum?minimum:0,
                max: maximum?maximum:10,
                value: select[0].selectedIndex,
                formatter: function(value) {
                    return 'Valeur : ' + value;
                }
            });
            var w = $('#q22').width();
            $('.slider-handle').text(select[0].selectedIndex);
            $('.slider').attr('style','width:'+w+'px;');
            /*var slider = $( "<div id='slider'></div>" ).insertAfter( select ).slider({
             min: minimum?minimum:0,
             max: maximum?maximum:10,
             range: 1,
             value: select[0].selectedIndex,
             formatter: function(value) {
             return 'Valeur : ' + value;
             }
             });*/

            /*slider.bind('slide', function(e){
             console.log('A',select[0].selectedIndex);
             select[0].selectedIndex = e.value ;
             console.log('B',select[0].selectedIndex);
             });
             slider.bind('click', function(e){
             console.log('C',e,select[0].selectedIndex);
             select[0].selectedIndex = e.value ;
             console.log('D',select[0].selectedIndex);
             });*/
            slider.bind('change', function(ev){
                var newVal = slider.data('slider').getValue();
                select[0].selectedIndex = newVal;
                $('.slider-handle').text(newVal);
            });
            /*slider.bind('change', function(e){
             console.log(e.slider('getValue'));
             console.log('E',e,e.value,e.newValue);
             select[0].selectedIndex = e.value ;
             console.log('F',select[0].selectedIndex);
             });*/


        });
    },

    // -----------------------------------------------------------------------------------------------------------------

    // Gestion des questions liées
    linkedQuestions: function(toggle) {
        $.each(toggle, function (index, value) {
            var form_question = $('#form_question_' + value.item);

            form_question.on('change', function () {
                selected($(this), value.check, value.parse);
            });

            selected(form_question, value.check, value.parse);
        });

        //Gère la possibilite de décocher une réponse.
        // Mis ici pour éviter des problème d'affichage lors de la déselection
        $("input[type='radio']").click(function () {
            var previousValue = $(this).attr('previousValue');
            var name = $(this).attr('id');

            if (previousValue == 'checked') {
                $(this).removeAttr('checked');
                $(this).attr('previousValue', false);
                $(this).trigger('change');
            }
            else {
                $("input[id=" + name + "]:radio").attr('previousValue', false);
                $(this).attr('previousValue', 'checked');
            }
        });

        function selected(item, arrayToCheck, arrayToParse)
        {
            $.each(arrayToParse, function(index, value)
            {

                var item_block = $('#tr_' + value);
                if(!isNaN(parseInt(item.find('option:selected').attr('value')))) {

                    if (0 !== $.inArray(parseInt(item.find('option:selected').attr('value')), arrayToCheck)) {
                        var item_value = $('#' + value);

                        item_block.fadeOut();

                        // Remise à blanc des champs des questions liées
                        item_value.find('textarea').val('');
                        item_value.find('input[type=text]').val('');
                        item_value.find('input[type=radio]').prop('checked', false);
                        item_value.find('input[type=checkbox]').prop('checked', false);
                        item_value.find('select').prop('selectedIndex', 0);
                    }
                    else {
                        item_block.fadeIn();
                    }

                }else{

                    if (0 !== $.inArray(parseInt(item.find('input:checked').attr('value')), arrayToCheck) || isNaN(parseInt(item.find('input:checked').attr('value')))) {

                        var item_value = $('#' + value);
                        item_block.fadeOut();

                        // Remise à blanc des champs des questions liées
                        item_value.find('textarea').val('');
                        item_value.find('input[type=text]').val('');
                        item_value.find('input[type=radio]').prop('checked', false);
                        item_value.find('input[type=checkbox]').prop('checked', false);
                        item_value.find('select').prop('selectedIndex', 0);
                    }
                    else {

                        item_block.fadeIn();
                    }
                }
            });
        }
    },

    // -----------------------------------------------------------------------------------------------------------------

    // Loader sur le submit du questionnaire
    submit_questionnaire: function()
    {

        $('#enregistrer_questionnaire, #valider_questionnaire').click(function()
        {
            var obs_score = 0;

            if($('input[name="form[question_q6]"]:checked').length==1)
                obs_score+=parseInt($('input[name="form[question_q6]"]:checked').data('score'));
            if($('input[name="form[question_q7]"]:checked').length==1)
                obs_score+=parseInt($('input[name="form[question_q7]"]:checked').data('score'));
            if($('input[name="form[question_q9]"]:checked').length==1)
                obs_score+=parseInt($('input[name="form[question_q9]"]:checked').data('score'));
            if($('input[name="form[question_q10]"]:checked').length==1)
                obs_score+=parseInt($('input[name="form[question_q10]"]:checked').data('score'));
            if($('input[name="form[question_q11]"]:checked').length==1)
                obs_score+=parseInt($('input[name="form[question_q11]"]:checked').data('score'));
            if($('input[name="form[question_q12]"]:checked').length==1)
                obs_score+=parseInt($('input[name="form[question_q12]"]:checked').data('score'));
            if($('input[name="form[question_q13]"]:checked').length==1)
                obs_score+=parseInt($('input[name="form[question_q13]"]:checked').data('score'));
            if($('input[name="form[question_q14]"]:checked').length==1)
                obs_score+=parseInt($('input[name="form[question_q14]"]:checked').data('score'));

            $('#form_obs_score').val(obs_score);

            if($('input[name="form[question_q16]"]:checked').length==1)
                $('#form_tolerance_score').val($('input[name="form[question_q16]"]:checked').data('score'));

            if($('input[name="form[question_q17]"]:checked').length==1)
                $('#form_question_q17_score').val($('input[name="form[question_q17]"]:checked').data('score'));
            if($('input[name="form[question_q18]"]:checked').length==1)
                $('#form_question_q18_score').val($('input[name="form[question_q18]"]:checked').data('score'));
            if($('input[name="form[question_q19]"]:checked').length==1)
                $('#form_question_q19_score').val($('input[name="form[question_q19]"]:checked').data('score'));
            if($('input[name="form[question_q20]"]:checked').length==1)
                $('#form_question_q20_score').val($('input[name="form[question_q20]"]:checked').data('score'));
            if($('input[name="form[question_q21]"]:checked').length==1)
                $('#form_question_q21_score').val($('input[name="form[question_q21]"]:checked').data('score'));

            $('#enregistrer_questionnaire').hide();
            $('#valider_questionnaire').hide();
            /*event.preventDefault();*/

        });
    },

    // -----------------------------------------------------------------------------------------------------------------

    // Ajoute une nouvelle ligne dans le tableau de récapitulatif des questionnaires de suivi
    add_new_line: function(historique_suivi_table, session_datas)
    {

        console.log(session_datas);


        var new_tr = $('<tr/>', {
            id      : session_datas.cure_questionnaire_id,
            'class' : 'success'
        }).prependTo(historique_suivi_table);

        var td_date  = $('<td/>', {
            'class' : 'cure_questionnaire_date',
            html    : session_datas.cure_date
        }).appendTo(new_tr);

        var td_label  = $('<td/>', {
            'class' : 'cure_questionnaire_href'
        }).appendTo(new_tr);

        var td_statut = $('<td/>', {
            'class' : 'cure_questionnaire_statut'
        })
            .appendTo(new_tr)
            .html(session_datas.cure_statut);

        var td_user = $('<td/>', {
            'class' : 'cure_questionnaire_user'
        })
            .appendTo(new_tr)
            .html(session_datas.action.username);

        return $('<a/>', {
            'class' : 'questionnaire_cure_reponses a_link',
            href    : session_datas.href,
            html    : session_datas.cure_label
        }).appendTo(td_label);
    },

    // -----------------------------------------------------------------------------------------------------------------

    // Update une ligne dans le tableau de récapitulatif des questionnaires de suivi
    update_new_line: function(historique_suivi_table, session_datas)
    {
        historique_suivi_table
            .find('tr[id="' + session_datas.cure_questionnaire_id + '"]')
            .addClass('success')
            .find('.cure_questionnaire_date')
            .html(session_datas.cure_date);
    },

    // -----------------------------------------------------------------------------------------------------------------

    // Ajoute une nouvelle ligne de preview dans le tableau de récapitulatif des questionnaires de suivi
    add_new_preview_line: function(historique_suivi_table, session_datas)
    {
        var new_tr = $('<tr/>', {
            id      : session_datas.cure_questionnaire_id,
            'class' : 'success'
        }).prependTo(historique_suivi_table);

        var td_date  = $('<td/>', {
            'class' : 'cure_questionnaire_date',
            html    : session_datas.cure_date
        }).appendTo(new_tr);

        var td_label  = $('<td/>', {
            'class' : 'cure_questionnaire_href'
        }).appendTo(new_tr);

        var td_statut = $('<td/>', {
            'class' : 'cure_questionnaire_statut'
        })
            .appendTo(new_tr)
            .html(session_datas.cure_statut);

        return $('<a/>', {
            'class' : 'questionnaire_cure_reponses_preview',
            href    : session_datas.href,
            html    : session_datas.cure_label
        }).appendTo(td_label);

    },

    // -----------------------------------------------------------------------------------------------------------------

    // Update une ligne de preview dans le tableau de récapitulatif des questionnaires de suivi
    update_preview_line: function(historique_suivi_table, session_datas)
    {
        var old_tr = historique_suivi_table
            .find('tr[id="' + session_datas.cure_questionnaire_id + '"]')
            .addClass('success');

        old_tr
            .find('.cure_questionnaire_date')
            .html(session_datas.cure_date);

        old_tr
            .find('.cure_questionnaire_statut')
            .html(session_datas.cure_statut);

        var cure_questionnaire_href = old_tr.find('.cure_questionnaire_href');

        cure_questionnaire_href.find('a').remove();

        $('<a/>', {
            'class' : 'questionnaire_cure_reponses_preview',
            href    : session_datas.href,
            html    : session_datas.cure_label
        }).appendTo(cure_questionnaire_href);
    },

    // -----------------------------------------------------------------------------------------------------------------

    // Ajout dans l'historique des actions
    add_action_historique: function(values)
    {
        var action_container = window.opener.$('#historique_patient_action_table');

        window.opener.$('#action_patient_no_results').remove();

        var tr = $('<tr/>', {
            id      : values.action_id,
            'class' : 'success'
        }).prependTo(action_container.find('tbody'));

        $('<td/>', {
            html    : values.dateCreation,
            'class' : 'historique_action_date'
        }).appendTo(tr);

        $('<td/>', {
            html    : values.actionType,
            'class' : 'historique_action_type'
        }).appendTo(tr);

        $('<td/>', {
            html    : values.actionStatut,
            'class' : 'historique_action_statut'
        }).appendTo(tr);

        $('<td/>', {
            html    : values.username,
            'class' : 'historique_action_user'
        }).appendTo(tr);
    },

    // -----------------------------------------------------------------------------------------------------------------

    // Ajout dans l'historique des actions
    add_action_historique_suivi: function(values)
    {
        var action_container = window.opener.$('#historique_patient_action_table');

        window.opener.$('#action_patient_no_results').remove();

        var tr = $('<tr/>', {
            id      : values.action_id,
            'class' : 'success'
        }).prependTo(action_container.find('tbody'));

        $('<td/>', {
            html    : values.dateCreation,
            'class' : 'historique_action_date'
        }).appendTo(tr);

        var td_action = $('<td/>', {
            html    : values.actionType,
            'class' : 'historique_action_type'
        }).appendTo(tr);

        $('<td/>', {
            html    : values.actionStatut,
            'class' : 'historique_action_statut'
        }).appendTo(tr);

        $('<td/>', {
            html    : values.username,
            'class' : 'historique_action_user'
        }).appendTo(tr);

        var ul = $('<ul/>',{}).appendTo(td_action);
        var li = $('<li/>',{}).appendTo(ul);

        return $('<a/>', {
            'class' : 'questionnaire_cure_reponses_preview',
            href    : values.que_href,
            html    : values.que_label
        }).appendTo(li);
    },

    // -----------------------------------------------------------------------------------------------------------------

    // Update dans l'historique des actions
    update_action_historique: function(values)
    {
        var action_container = window.opener.$('#historique_patient_action_table');

        var tr = action_container.find('tr[id="' + values.action_id + '"]');

        tr.addClass('success');

        tr.find('td.historique_action_date').html(values.dateCreation);
        tr.find('td.historique_action_type').html(values.actionType);
        tr.find('td.historique_action_statut').html(values.actionStatut);
        tr.find('td.historique_action_user').html(values.username);
    },


};

$(function()
{
    questionnaire.init();
});