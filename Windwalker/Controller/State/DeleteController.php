<?php

namespace Windwalker\Controller\State;

/**
 * Class TrashController
 *
 * @since 1.0
 */
class DeleteController extends AbstractUpdateStateController
{
	/**
	 * Property stateData.
	 *
	 * @var string
	 */
	protected $stateData = array(
		'published' => '-9'
	);

	/**
	 * Property actionText.
	 *
	 * @var string
	 */
	protected $actionText = 'DELETED';

	/**
	 * doUpdate
	 *
	 * @return boolean
	 */
	public function doUpdate()
	{
		$pks = $this->cid;

		foreach ($pks as $i => $pk)
		{
			$this->table->reset();

			if ($this->table->load($pk))
			{
				if (!$pk)
				{
					unset($pks[$i]);

					continue;
				}

				if (!$this->allowDelete($this->table->getProperties(true)))
				{
					// Prune items that you can't change.
					unset($pks[$i]);

					$this->setMessage(\JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
				}
			}
		}

		if (!$this->model->delete($pks))
		{
			return false;
		}

		$errors = $this->model->getState()->get('error.message');

		if (count($errors))
		{
			$this->setMessage(implode('<br />', $errors));
		}
	}
}
