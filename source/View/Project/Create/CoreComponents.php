<?=(isset($header) && (!isset($ajax) || $ajax!==true)?$header:"")?>
<div class="container" class="<?=$page?>">
    <h3>Укажите требуемые компоненты ядра.</h3>
    <p>Компоненты данного раздела будут доступны через основной статический класс <strong>\Registry</strong>. Ниже перечислены предустановленные компоненты.</p>
    <p>Для добавления собственных воспользуйтесь мастером, кликнув кнопку "Новый компонент". Будет создан пустой компонент, который следует описать детальнее в любом PHP редакторе.</p>
    <p>Выполните добавление компонентов в проект, кликнув кнопку "Добавить" напротив нужного компонента. Наберите требуемое число компонентов.
    Один и тот же компонент можно подключить несколько раз с разной конфигурацией, например для создания нескольких подключений к разным базам данных.</p>
    <div class="alert alert-warning"><i class="fa fa-warning"></i> Для старта любого проекта требуется необходимый набор компонентов, которые уже добавлены и нельзя удалить. 
    Также при добавлении некоторых компонентов связанные с ним будут добавленны автоматически.</div>
    <div id="core_group" class="panel-group">
    <?php foreach ($CoreList as $file) foreach ($file as $class){ ?>
        <div class="panel panel-default">
            <div class="panel-heading item-collapse">
                <div class="pull-right">
                    <button class="btn btn-primary" title="Добавить"><i class="fa fa-plus"></i></button>
                </div>
                <span data-toggle="collapse" data-target="#<?=$class['name']?>_info" data-parent="#core_group" aria-expanded="false">
                    <b><?=$class['name']?></b>: <?=$class['info']['title']?>
                </span>
            </div>
            <div id="<?=$class['name']?>_info" class="panel-collapse collapse" aria-expanded="false">
                <div class="panel-body">
                    <p><?=$class['info']['description']?></p>
                    <label class="control-label">Свойства:</label>
                    <ul id="<?=$class['name']?>_prop_group" class="list-group">
                    <?php foreach ($class['properties'] as $property){ ?>
                        <li class="list-group-item item-collapse">
                            <div class="panel-heading" data-toggle="collapse" data-target="#<?=$class['name']?>_<?=$property['name']?>_prop_info" data-parent="#<?=$class['name']?>_prop_group" aria-expanded="false">
                                <b><?=$property['name']?></b>: <?=$property['info']['title']?>
                            </div>
                            <div id="<?=$class['name']?>_<?=$property['name']?>_prop_info" class="panel-collapse collapse" aria-expanded="false">
                                <div class="panel-body">
                                    <p><?=$property['info']['description']?></p>
                                </div>
                            </div>
                        </li>
                    <?php } ?>
                    </ul>
                    <label class="control-label">Функции:</label>
                    <ul id="<?=$class['name']?>_method_group" class="list-group">
                    <?php foreach ($class['methods'] as $method){ ?>
                        <li class="list-group-item item-collapse">
                            <div class="panel-heading" data-toggle="collapse" data-target="#<?=$class['name']?>_<?=$method['name']?>_method_info" data-parent="#<?=$class['name']?>_method_group" aria-expanded="false">
                                <b><?=$method['name']?></b>: <?=$method['info']['title']?>
                            </div>
                            <div id="<?=$class['name']?>_<?=$method['name']?>_method_info" class="panel-collapse collapse" aria-expanded="false">
                                <div class="panel-body">
                                    <p><?=$method['info']['description']?></p>
                                </div>
                            </div>
                        </li>
                    <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php } ?>
    </div>
</div>
<?=(isset($footer) && (!isset($ajax) || $ajax!==true)?$footer:"")?>