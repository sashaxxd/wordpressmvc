<?php
namespace App\Menu\Top;

/*
 * Основная Mysql таблица модуля
 */
class SqlTable extends \Wdpro\BaseSqlTable {

	/**
	 * Имя таблицы
	 *
	 * @var string
	 */
	protected static $name = 'menu_top';

	/**
	 * Структура таблицы
	 *
	 * <pre>
	 * return [
	 *  static::COLLS => [
	 *      'section_type'=>'varchar(40)',
	 *      'section_id'=>'int',
	 *  ],
	 * ];
	 * </pre>
	 *
	 * @return array array('sql'=>'CREATE TABLE ...', 'format'=>array('field_name'=>'%d')
	 */
	protected static function structure() {

		return [
			static::COLLS => [
				'id',
				'post_parent'=>'int', // Это и для страниц и для простых элементов
				'menu_order'=>'int',
				'post_status',
				'post_title',
				'post_name',

			],

			static::ENGINE => static::INNODB,
		];
	}


}