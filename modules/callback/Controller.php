<?php
namespace Wdpro\Callback;

class Controller extends \Wdpro\BaseController {
	
	public static function init() {

		\Wdpro\Autoload::add('Wdpro\Callback', __DIR__);

		// Подключаем окошки
		\Wdpro\Modules::addWdpro('dialog');

		SqlTable::init();

		// Ajax
		wdpro_ajax('callback-form', function ()
		{
			$data = $_POST;
			if (!$data['form_text']) {
				$data['form_text'] = '<p>Имя: '.$data['name'].'</p>'
				                   .'<p>Телефон: '.$data['phone'].'</p>';
			}

			// Сохраняем
			$callback = new Entity();
			$callback->setData(array(
				'data'=>$data,
			));
			$callback->save();
			
			// Отправляем сообщение админам
			\Wdpro\AdminNotice\Controller::sendMessageHtml(
				'Заказ обратного звонка',
				$data['form_text']
			);
			
			// Отправляем событие о заказе звонка
			do_action('wdpro_callback', [
				'data'=>$data,
			]);

			return array(
				'message'=>get_option('callback_message'),
			);
		});

	}
	
	
	public static function run() {
		
		\Wdpro\Console\Menu::add(array(
			'roll'=>ConsoleRoll::class,
			'icon'=>'dashicons-phone',
			'position'=>30,
			'showNew'=>true,
		));
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (в админке)
	 */
	public static function runConsole() {

		// Настройки
		\Wdpro\Console\Menu::addSettings('Заказ обратного звонка', function ($form) {

			/** @var \Wdpro\Form\Form $form */
			$form->add(array(
				'name'=>'callback_message',
				'left'=>'Текст после отправки формы',
				'type'=>'ckeditor',
			));
			$form->add('submitSave');

			return $form;
		});
	}


}


return __NAMESPACE__;