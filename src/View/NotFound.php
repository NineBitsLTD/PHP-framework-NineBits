<?=(isset($header) && (!isset($ajax) || $ajax!==true)?$header:"")?>
    <div class="container" class="<?=$page?>">
        <h1>Страница не найдена</h1>
        <?php if(isset($msg)){ ?>
        <h4><?=(string)$msg?></h4>
        <?php } ?>
    </div>
<?=(isset($footer) && (!isset($ajax) || $ajax!==true)?$footer:"")?>
