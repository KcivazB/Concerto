$(document).ready(function() {
    // Function to handle modal opening and content loading
    function openModal(modalSelector, contentUrl, contentContainerSelector) {
        $(modalSelector).modal('show');
        $(contentContainerSelector).html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
        $.get(contentUrl, function(data) {
            $(contentContainerSelector).html(data);
        });
    }

    // Click event handlers for different modals
    $('.modalbox').on('click', function(event) {
        event.preventDefault();
        var contentUrl = $(this).data('content-url');
        openModal('#ideaModal', contentUrl, '#modalContent');
    });

    $('.modalbox-comments').on('click', function(event) {
        event.preventDefault();
        var contentUrl = $(this).data('content-url');
        openModal('#commentsModal', contentUrl, '#commentsModalContent');
    });

    $('.modalbox-project').on('click', function(event) {
        event.preventDefault();
        var contentUrl = $(this).data('content-url');
        openModal('#projectModal', contentUrl, '#projectModalContent');
    });

    $('.modalbox-edit-project').on('click', function(event) {
        event.preventDefault();
        var contentUrl = $(this).data('content-url');
        openModal('#editProjectModal', contentUrl, '#editProjectModalContent');
    });

    $('.modalbox-edit-idea').on('click', function(event) {
        event.preventDefault();
        var contentUrl = $(this).data('content-url');
        openModal('#editIdeaModal', contentUrl, '#editIdeaModalContent');
    });

    $('.modalbox-invite-member').on('click', function(event) {
        event.preventDefault();
        var contentUrl = $(this).data('content-url');
        openModal('#inviteMemberModal', contentUrl, '#inviteMemberModalContent');
    });

    // Ensure only one modal is shown at a time
    $('.modal').on('hidden.bs.modal', function() {
        $(this).modal('hide');
    });

    $('.modalbox-edit-profile').on('click', function(event) {
        event.preventDefault();
        var contentUrl = $(this).data('content-url');
        openModal('#profileEditModal', contentUrl, '#profileEditModalContent');
    });

    // Handle form submission inside modals
    $('#ideaModal, #commentsModal, #editProjectModal, #editIdeaModal, #inviteMemberModal, #profileEditModal').on('submit', 'form', function(event) {
        event.preventDefault();
        
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        
        // Disable submit button to prevent multiple submissions
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Submitting...');
        
        $.ajax({
            type: form.attr('method'),
            url: form.attr('action'),
            data: form.serialize(),
            success: function(response) {
                // Hide modal after successful submission
                form.closest('.modal').modal('hide');
                // Handle success response as needed
                // For example, update the relevant part of the page
                window.location.reload();
            },
            error: function(xhr) {
                // Handle errors
                console.log(xhr.responseText);
            },
            complete: function() {
                // Re-enable submit button after request completes (success or error)
                submitBtn.prop('disabled', false).html('Submit');
            }
        });
    });
});
