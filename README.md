# console-log
**Простой класс для создания роботов**

Пример простейшего робота:

```php
<?php
/**
 * Пример создания роботов
 */

namespace {
    
}

namespace bot {

    use Galantcev\Components\Bot;

    /**
     * Пример робота
     * Class SampleBot
     * @package bot
     */
    class SampleBot extends Bot
    {
        /**
         * Робот что-нибудь делает
         */
        public function go()
        {
            // Устанавливаем коллбек в случае некорректного завершения робота
            $this->start(function () {
                die('А вот тут у нас случилось прерывание, а ничего успешно не завершено!');
            });

            $this->log->text('Текст');
            $this->log->error('Ошибка!');
            $this->log->warning('Внимание.');
            $this->log->success('Успех :)');

            // А здесь говорим, что на самом деле всё хорошо отработало
            $this->finish();
        }
    }

    $bot = new SampleBot();

    $bot->log->text('Начинаем работу!');

    $bot->go();

    $bot->log->text('Завершаем работу.');
}
```