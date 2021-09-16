<!DOCTYPE html>
<html>
<head>
    <script type="text/javascript" src="http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_SVG"></script>
</head>
<body>
    <?php foreach ($qas as $index => $qa):?>

        <h5><?= ($index + 1) ?>. <?= $qa['question'] ?></h5>
        <p><?= $qa['solution'] ?></p>

    <?php endforeach;?>
</body>
</html>