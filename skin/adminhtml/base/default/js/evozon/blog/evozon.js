/* Comments and subcomments list */

// function to redirect to edit action from comment controller
function editComment(url) {
    window.location.href = url;
}

// function to redirect to delete action from comment controller
function deleteComment(url) {
// confirm the delete action
    if (window.confirm(Translator.translate('Are you sure you want to delete this comment?'))) {
        window.location.href = url;
    }

    return false;
}

// get all the subcomments for a comment/subcomment
function getSubcomments(commentId, subcommentsNr, url) {
    // if the subcomments were loaded show them, else make an ajax call
    if ($('children-' + commentId).innerHTML.length > 0) {
        $('subcomments_button-' + commentId).hide();
        $('hide_subcomments_button-' + commentId).show();
        $('children-' + commentId).show();
    } else {
        new Ajax.Request(url, {
            method: 'post',
            parameters: {
                commentId: commentId,
                subcommentsNr: subcommentsNr
            },
            onSuccess: function (transport) {
                if (transport.responseText.isJSON()) {
                    $('children-' + commentId).update(transport.responseText.evalJSON());
                    $('subcomments_button-' + commentId).hide();
                    $('hide_subcomments_button-' + commentId).show();
                }
            },
            onFailure: function () {
                alert(Translator.translate('Something went wrong.'));
            }
        });
    }
}

// hide subcomments and display show subcomments button
function hideSubcomments(commentId) {
    $('children-' + commentId).hide();
    $('hide_subcomments_button-' + commentId).hide();
    $('subcomments_button-' + commentId).show();
}

function toggleContent(commentId) {
    $('comment_subject_read_more_' + commentId).toggle();
    $('comment_show_less_' + commentId).toggle();
}

// change the status for comment
function commentChangeStatus(commentId, url) {
    var statusesSelect = $("comment_statuses-" + commentId),
            status = statusesSelect.options[statusesSelect.selectedIndex].value,
            message = Translator.translate('Something went wrong.'),
            changeStatusMessage = $("change_status_message-" + commentId);

    new Ajax.Request(url, {
        method: 'post',
        parameters: {
            commentId: commentId,
            status: status
        },
        onSuccess: function (transport) {
            if (transport.responseText.isJSON() && transport.responseText.evalJSON() === 'SUCCESS') {
                message = Translator.translate('The status was successfully updated!');
                changeStatusMessage.setStyle({color: 'green'});
            }

            changeStatusMessage.update(message);
        },
        onFailure: function () {
            changeStatusMessage.setStyle({color: 'red'});
            changeStatusMessage.update(message);
        },
        onComplete: function () {
            setTimeout(function () {
                changeStatusMessage.update('');
            }, 4000);
        }
    });


}

var windowObjectReference;
var strWindowFeatures = "menubar=no,location=no,toolbar=no,resizable=yes,scrollbars=yes,status=yes,height=400px,width=800px";

function openRequestedPopup(anchor, title) {
    windowObjectReference = window.open("<?php echo $this->getHelpUrl(); ?>", title, strWindowFeatures);
}

//used on the checkbox in system_config; 
//it will toggle text field inputs to 0 and set it disabled if the checkbox is clicked
function toggleAll(id) {
    var configInput = $(id),
        showAllCheckbox = $('all_' + id);

    if (showAllCheckbox.checked === true) {
        configInput.value = 0;
        configInput.readOnly = true;
    } else {
        configInput.readOnly = false;
    }
}

function onCustomUseDefaultChanged(element) {
    var useDefault = (element.value == 1) ? true : false;
    element.up(2).select('input', 'select', 'textarea').each(function(el){
        if (element.id != el.id) {
            el.disabled = useDefault;
        }
    });
    element.up(2).select('img').each(function(el){
        if (useDefault) {
            el.hide();
        } else {
            el.show();
        }
    });
}