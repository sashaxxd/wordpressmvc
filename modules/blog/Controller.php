<?php
namespace Wdpro\Blog;

use App\Blog\SqlTable;

class Controller extends \Wdpro\BaseController {

	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite() {

		add_shortcode('blog_posts', function () {
			
			$post = get_post();
			
			return Roll::getHtml(
				['WHERE `post_status`="publish" AND `post_parent`=%d
				AND `in_menu`=1
				ORDER BY `menu_order` DESC', $post->ID]
			);
		});
	}


	/**
	 * Дополниительная инициализация для админки
	 *
	 * Иконки: https://developer.wordpress.org/resource/dashicons/#forms
	 */
	public static function initConsole() {

		\Wdpro\Console\Menu::addSettings('Блог', function ($form) {
			
			/** @var \Wdpro\Form\Form $form */
			
			// Отправка события на дополнительную инициализацию формы настроек
			do_action('wdpro_blog_init_console', $form);
			
			$form->add([
				'name'=>'blog_codes',
				'top'=>'Вставить html код под статью',
				'type'=>$form::TEXT,
				'width'=>600,
			]);
			$form->add($form::SUBMIT_SAVE);
			
			return $form;
		});
	}


	/**
	 * Дополнительная обработка формы
	 * 
	 * @param callback $callback Каллбэк, принимающий форму
	 */
	public static function initForm($callback) {
		
		add_action('blog_console_form', $callback);
	}


}

return __NAMESPACE__;