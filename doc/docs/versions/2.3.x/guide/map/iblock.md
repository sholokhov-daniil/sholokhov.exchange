# Свойство информационного блока

Класс: **Sholokhov\Exchange\Fields\IBlock\IBlockElementField**

Класс является наследником [стандартного свойства](/2.1.x/guide/map/) и поведение идентично ему.

Текущее свойство предназначено для описания [пользовательских свойств](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=3399) информационного блока,
и используется импортом **Sholokhov\Exchange\Target\Import\IBlock\Element**

Если импорт в ходе следующих действий встречает класс, который реализовывает интерфейс **Sholokhov\Exchange\Fields\IBlock\ElementFieldInterface**, то работает как со свойством:
- Преобразование значения
- Запись значений свойств
