<?=(isset($header) && (!isset($ajax) || $ajax!==true)?$header:"")?>
<div class="container" class="<?=$page?>"></div>
<?=(isset($footer) && (!isset($ajax) || $ajax!==true)?$footer:"")?>