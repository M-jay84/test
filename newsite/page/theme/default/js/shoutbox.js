<script type="text/javascript">
    var frm = $('#contactForm1');

    frm.submit(function(e) {

        e.preventDefault();

        $.ajax({
            type: frm.attr('method'),
            url: frm.attr('action'),
            data: frm.serialize(),
            success: function(data) {
                console.log('Submission was successful.');
            },
            complete: function() {
                $("#message").focus().val('');
                $('#shoutbox').load('<?php echo URLROOT; ?>/shoutbox/chat');
            },
            error: function(data) {
                console.log('An error occurred.');
            },
        });
    });
</script>

<script>
    function updateShouts() {
        // Assuming we have #shoutbox
        $('#shoutbox').load('<?php echo URLROOT; ?>/shoutbox/chat');
    }
    setInterval("updateShouts()", 300000);
    updateShouts();
</script>