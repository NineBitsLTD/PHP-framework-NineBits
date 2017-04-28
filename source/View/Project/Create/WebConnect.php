<?=(isset($header) && (!isset($ajax) || $ajax!==true)?$header:"")?>
<div class="container" class="<?=$page?>">
    <h3>Подключитесь к Web серверу.</h3>
    <p>Укажите данные FTP для доступа к Web серверу. Или укажите HTTP адрес сервера и загрузите на сервер файл php, через который генератор сможет развернуть Web проект.</p>
</div>
<?=(isset($footer) && (!isset($ajax) || $ajax!==true)?$footer:"")?>