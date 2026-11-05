<?php
declare(strict_types=1);

/**
 * URL: /demo/forms
 * Shows form helpers; posts to demo/forms/submit
 */

theme_header('Demo – Forms');
render_flash();

block_begin('Form helpers');

form_start(url('demo/formssubmit'), 'post');
echo csrf_field();

form_input('name', 'Name', '', 'text', 'Your name', true);
form_input('email', 'Email', '', 'email', 'you@example.com');
form_textarea('notes', 'Notes', '', 4, 'Optional notes');
form_select('colour', 'Colour', [
    ''      => '— choose —',
    'green' => 'Green (success)',
    'yellow'=> 'Yellow (warning)',
    'blue'  => 'Blue (info)',
], '');
form_checkbox('agree', 'I agree to the demo terms', '1', false);
form_hidden('source', 'demo-forms');

echo '<div class="text-center">';
button('Submit demo form');
button_link('Cancel', url('home'), 'ttbtn');
echo '</div>';

form_end();
block_end();

echo '<p class="text-center"><a href="' . url('home') . '">Back home</a></p>';
theme_footer();