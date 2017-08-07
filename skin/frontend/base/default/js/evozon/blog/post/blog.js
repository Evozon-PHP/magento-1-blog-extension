var Comment = Class.create();

Comment.prototype = {
    initialize: function () {
    },
    // show the form for adding a new comment
    showAddCommentForm: function (commentId) {
        if ($('post_comment_form-' + commentId)) {
            $('post_comment_form-' + commentId).toggle();
        } else {
            var clone = $('post_comment_form').clone(true);
            clone.writeAttribute('id', 'post_comment_form-' + commentId);
            clone.writeAttribute('name', commentId);
            clone.getInputs('hidden', 'parent_id')[0].value = commentId;

            $('post_comment_reply-' + commentId).insert({'after': clone});
        }

        //hiding or showing the reply links

        var clickedLink = $('post_comment_reply_link-' + commentId);
        if (clickedLink.hasClassName('clicked')) {
            clickedLink.removeClassName('clicked');
            var showAll = true;
        } else {
            clickedLink.addClassName('clicked');
        }

        $$('.post_comment_reply_link').each(function (element) {
            if (showAll == true) {
                element.show();
            } else {
                if (element.readAttribute('id') != 'post_comment_reply_link-' + commentId) {
                    element.hide();
                }
            }
        });
    },
    // add new comment
    addComment: function (object) {
        var currentCommentId = $(object).up(1).readAttribute('name'),
            addCommentForm = $('post_comment_form' + (currentCommentId ? '-' + currentCommentId : '')),
            validator = new Validation(addCommentForm);

        $$('.post_comment_error_message', '.post_comment_info_message').each(function (element) {
            element.remove();
        });

        // validate the form
        if (validator.validate()) {

            // showing the reply links
            $$('.post_comment_reply_link').each(function (element) {
                element.show();
            });

            // ajax call to add the comment
            new Ajax.Request(addCommentForm.readAttribute('action'), {
                method: 'POST',
                parameters: addCommentForm.serialize(),
                onLoading: function () {
                    addCommentForm.getInputs('button')[0].disabled = true;
                },
                onSuccess: function (transport) {
                    if (transport.responseText.isJSON()) {
                        var response = transport.responseText.evalJSON();
                        // verify if the response is a message
                        if (response.message) {
                            addCommentForm.reset();
                            var infoMessage = new Element('p', {
                                'class': 'post_comment_info_message',
                                'id': 'info-message-' + currentCommentId
                            }).update(response.message);

                            var replyDiv = $('post_comment_reply' + (currentCommentId ? '-' + currentCommentId : ''));
                            replyDiv.insert({'after': infoMessage});
                        }

                        // verify if the response is the comments block
                        if (response.comments) {
                            $('post_comments').update(response.comments);
                            $('metadata_comments_number').innerHTML = $('header_comments_number').innerHTML;
                        }

                        // verify if the response is an error
                        if (response.error) {
                            var errorObject = response.error;
                            if (errorObject.form_key) {
                                var errorMessage = new Element('p', {
                                    'class': 'post_comment_error_message',
                                    'id': 'error-message-' + currentCommentId
                                }).update(errorObject.form_key);
                                addCommentForm.insert({'after': errorMessage});
                            } else {
                                var prop;
                                for (prop in errorObject) {
                                    var messageId = 'error-message-' + prop + '-' + currentCommentId;
                                    var data = errorObject[prop];
                                    if (Array.isArray(data)) {
                                        var errorMessage = new Element('p', {
                                            'class': 'post_comment_error_message',
                                            'id': messageId
                                        }).update(data);
                                        $$('#post_comment_form' + (currentCommentId ? '-' + currentCommentId : '') + ' #' + prop).first().insert({'after': errorMessage});
                                    } else {
                                        $H(data).each(function (pair) {
                                            var errorMessage = new Element('p', {
                                                'class': 'post_comment_error_message',
                                                'id': messageId
                                            }).update(pair.value);
                                            $$('#post_comment_form' + (currentCommentId ? '-' + currentCommentId : '') + ' #' + prop).first().insert({'after': errorMessage});
                                        });
                                    }
                                }

                            }
                        }
                    }
                },
                onFailure: function () {
                    alert(Translator.translate('The comment was not submitted. Please try again!'));
                },
                onComplete: function () {
                    addCommentForm.getInputs('button')[0].disabled = false;
                }
            });
        }
    },
    // update comment
    updateComments: function (url, id) {
        new Ajax.Request(url, {
            method: 'POST',
            parameters: {
                postId: id
            },
            onSuccess: function (transport) {
                if (transport.responseText.isJSON()) {
                    $('post_comments').update(transport.responseText.evalJSON());

                    //adding onclick events to the pagination href
                    $$(".pages a").each(function (element) {
                        var pageElement = element.href.split('?')[1];
                        //getting page number
                        var pageNumber = pageElement.split('&')[2];
                        element.href = 'javascript:void(0);';
                        var actionUrl = url.substring(0, url.lastIndexOf('&')) + "&" + pageNumber;
                        element.writeAttribute("onclick", "comment.updateComments('" + actionUrl + "'," + id + ")");
                    });
                }
            },
            onFailure: function () {
                alert(Translator.translate('Something went wrong'));
            }
        });
    },
    checkIsCustomer: function (elem, url) {
        var email = elem.value;
        var commentForm = $('reply_button');
        commentForm.disabled = true;
        if (email) {
            new Ajax.Request(url, {
                method: 'POST',
                parameters: {
                    email: email
                },
                onSuccess: function (transport) {
                    if (transport.responseText.isJSON()) {
                        var response = transport.responseText.evalJSON();
                        var isCustomerElem = $(elem).up('form').down('.is_customer');
                        if (response.is_customer) {
                            isCustomerElem.show();
                            isCustomerElem.innerHTML = response.message;
                        } else {
                            isCustomerElem.hide();
                            commentForm.disabled = false;
                        }
                    }
                },
                onFailure: function () {
                    alert(Translator.translate('Something went wrong'));
                    commentForm.disabled = false;
                    $('is_customer').hide();
                }
            });
        }
    },
    // comment input validation
    validateLength: function (v, elm) {
        var reMax = new RegExp(/^maximum-length-[0-9]+$/);

        var result = true;
        $w(elm.className).each(function (name) {
            if (name.match(reMax) && result) {
                var length = name.split('-')[2];
                result = (v.length <= length);
            }
        });

        return result;
    }
};

document.observe('dom:loaded', function () {
    $$('h2.article-name a').each(function (element) {
        var parent = element.up();
        if (element.getHeight() > parent.getHeight()) {
            var child = parent.childElements('a');
            child[0].addClassName('shorter')
        }
    });
    $$('.recent-post-name').each(function (element) {
        var postTitle = element.childElements()[1] ? element.childElements()[1] : element.childElements()[0];
        if (postTitle.getHeight() > 19) {
            postTitle.addClassName('shorter')
        }
    });
});
