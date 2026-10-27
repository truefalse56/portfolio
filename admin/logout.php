<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';
logout();
redirect(base_url('admin/login.php'));
