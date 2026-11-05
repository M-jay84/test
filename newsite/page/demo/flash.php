<?php
declare(strict_types=1);

/** URL: /demo/flash — green flash on home */
redirect('home', 'Flash success: this is a green message after redirect.', 'success');