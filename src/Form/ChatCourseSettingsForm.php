<?php

declare(strict_types=1);
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

namespace ILIAS\Plugin\MatrixChatClient\Form;

use ilPropertyFormGUI;
use ilCheckboxInputGUI;
use ILIAS\DI\Container;
use ilMatrixChatClientPlugin;
use ILIAS\Plugin\MatrixChatClient\Controller\ChatCourseSettingsController;
use ilMatrixChatClientUIHookGUI;
use ilUIPluginRouterGUI;
use ilUtil;

/**
 * Class ChatCourseSettingsForm
 * @package ILIAS\Plugin\MatrixChatClient\Form
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ChatCourseSettingsForm extends ilPropertyFormGUI
{
    /**
     * @var ilMatrixChatClientPlugin
     */
    private $plugin;
    /**
     * @var Container
     */
    private $dic;

    public function __construct()
    {
        parent::__construct();
        $this->plugin = ilMatrixChatClientPlugin::getInstance();
        global $DIC;
        $this->dic = $DIC;
        $query = $this->dic->http()->request()->getQueryParams();
        $this->setTitle($this->plugin->txt("matrix.chat.course.settings"));

        $enableChatIntegration = new ilCheckboxInputGUI(
            $this->plugin->txt("matrix.chat.course.enable"),
            "chatIntegrationEnabled"
        );
        $enableChatIntegration->setRequired(true);
        $this->addItem($enableChatIntegration);

        if (!isset($query["ref_id"]) || !$query["ref_id"]) {
            ilUtil::sendFailure($this->plugin->txt("required_parameter_missing"), true);
            $this->plugin->redirectToHome();
        }

        $this->ctrl->setParameterByClass(
            ilMatrixChatClientUIHookGUI::class,
            "ref_id",
            (int) $query["ref_id"]
        );
        $this->setFormAction($this->ctrl->getFormActionByClass([
            ilUIPluginRouterGUI::class,
            ilMatrixChatClientUIHookGUI::class
        ], ChatCourseSettingsController::getCommand("showSettings")));

        $this->addCommandButton(
            ChatCourseSettingsController::getCommand("saveSettings"),
            $this->lng->txt("save")
        );
    }
}
