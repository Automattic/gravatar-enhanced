#!/bin/sh
sed "s/<?php /<?php\n\n\/\/ phpcs:ignore\n/" build/hovercards.asset.php | sed "s/);/);/" > build/hovercards.asset.php.new
mv build/hovercards.asset.php.new build/hovercards.asset.php

sed "s/<?php /<?php\n\n\/\/ phpcs:ignore\n/" build/quick-editor.asset.php | sed "s/);/);/" > build/quick-editor.asset.php.new
mv build/quick-editor.asset.php.new build/quick-editor.asset.php
