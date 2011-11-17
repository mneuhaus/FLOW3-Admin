Actions
#######

Actions for the Admin need to implement the following interaface::

	namespace Admin\Core\Actions;
	interface ActionInterface {

		/**
		 * Function to Check if this Requested Action is supported
		 * @author Marc Neuhaus <mneuhaus@famelo.com>
		 * */
		public function canHandle($being, $action = null, $id = false);

		/**
		 * The Name of this Action
		 * @author Marc Neuhaus <mneuhaus@famelo.com>
		 * */
		public function __toString();

		/**
		 * @param string $being
		 * @param array $ids
		 * @author Marc Neuhaus <mneuhaus@famelo.com>
		 * */
		public function execute($being, $ids = null);
	
	}

**Description of the functions**

.. function:: canHandle ($being, $action = null, $id = false)
    This function receives 3 arguments, based on which you need to decide if this action can handle the current use case.

    :param $being: represents the current class
    :param $action: name of current action (list, view, create, update, bulk,...)
    :param $id: specifies if this action will receive ids as well

.. function:: __toString ()
    This functions returns a Name for this action that will be used for the Buttons and such

.. function:: execute ($being, $ids = null)
    This function handles the execution of the action.

    :param $being: represents the current class
    :param $ids: an array of ids to act upon

Examples
********

The Delete action needs $ids to delete, so it returns true if there are ids to receive::

    class DeleteAction extends \Admin\Core\Actions\AbstractAction {
        public function canHandle($being, $action = null, $id = false) {
            return $id;
        }
    }

The Update action needs $ids to update, but can't handle bulk actions::

    class UpdateAction extends \Admin\Core\Actions\AbstractAction {
        public function canHandle($being, $action = null, $id = false) {
            switch($action) {
                case "bulk":
                    return false;
                default:
                    return $id;
            }
        }
    }

Rendering a view for the action
*******************************

The function execute behaves exactly like a regular controllerAction.
The following variables are defined in the ActionClass:

$this->request
	the regular controllerRequest
	
$this->view
	the view to be rendered
	
$this->adapter
	the current adapter to handle objects
	
$this->controller
	the responsible controller