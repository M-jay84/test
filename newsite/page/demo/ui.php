<?php
declare(strict_types=1);

/**
 * URL: /demo/ui — showcase style helpers
 */

theme_header('Demo – UI kit');
render_flash();

block_begin('Alerts');
alert_success('Green — success');
alert_warning('Yellow — warning / bad input');
alert_info('Blue — info / soft notice');
alert_error('Red — system error style');
block_end();

block_begin('Table');
table_start();
table_head(['ID', 'Name', 'Status']);
table_row(['1', e('Alpha'), badge('ok', 'success')]);
table_row(['2', e('Beta'), badge('wait', 'warning')]);
table_end();
block_end();

card_start('Card + progress');
echo '<p>Progress helper:</p>';
progress_bar(65, '65%');
spacer(10);
divider();
text_center('<a class="ttbtn" href="' . url('home') . '">Back home</a>');
card_end();

theme_footer();