<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?=$title;?></title>
</head>
<body>
<?php include "./cache/558f075cdf2d164b92b3eda3b3c71363.php";?>
<h2><?=$welcome;?></h2>
<?php if($welcome):?>
<p>this is the if </p>
<?php endif;?>
<ul>
    <?php foreach ($list as $k=>$val):?>
    <li><?=$val['title'];?>-<?=$val['content'];?>-<?=$val['add_time'];?></li>
    <?php endforeach;?>
</ul>
<dt>
    <dd>1111</dd>
</dt>
</body>
</html>