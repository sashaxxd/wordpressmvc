<?php echo '<?php'.PHP_EOL; ?>
namespace <?php echo $data['namespace']; ?>;

class ConsoleForm extends \<?=(
	$data['parent_namespace'] == 'App' ? 'App\BaseForm' : 'Wdpro\Form\Form'
)?> {

	/**
	 * Инициализация полей
	 *
	 * Здесь поля добавляются в дочерних классах через $this->add(array(...)) когда они
	 * не добавлены через конструктор
	 */
	protected function initFields() {

<?php if ($type == 'page'): ?>
		// Текст страницы
		$this->add([
		'name' => 'post_content[lang]',
		'top' => 'Текст страницы',
		'type' => static::CKEDITOR,
		]);
<?php endif; ?>

	}


}