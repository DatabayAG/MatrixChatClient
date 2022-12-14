<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;
use ILIAS\FileUpload\FileUpload;
use ILIAS\Plugin\MatrixChatClient\Form\PluginConfigForm;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilMatrixChatClientConfigGUI
 *
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ilMatrixChatClientConfigGUI extends ilPluginConfigGUI
{
    /**
     * @var ilObjUser
     */
    protected $user;
    /**
     * @var ilLogger
     */
    protected $logger;
    /**
     * @var FileUpload
     */
    protected $upload;
    /**
     * @var ilMatrixChatClientPlugin
     */
    protected $plugin;
    /**
     * @var ilTabsGUI
     */
    protected $tabs;
    /**
     * @var Container
     */
    protected $dic;
    /**
     * @var ilGlobalPageTemplate
     */
    protected $mainTpl;
    /**
     * @var ilLanguage
     */
    protected $lng;
    /**
     * @var ilCtrl
     */
    private $ctrl;

    /**
     * @throws ilPluginException
     */
    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
        $this->lng = $this->dic->language();
        $this->ctrl = $this->dic->ctrl();
        $this->mainTpl = $this->dic->ui()->mainTemplate();
        $this->tabs = $this->dic->tabs();
        $this->upload = $this->dic->upload();
        $this->logger = $this->dic->logger()->root();
        $this->user = $this->dic->user();

        $this->plugin = ilPlugin::getPluginObject(
            $_GET["ctype"],
            $_GET["cname"],
            $_GET["slot_id"],
            $_GET["pname"]
        );
        $this->plugin->denyConfigIfPluginNotActive();
    }

    /**
     * Show the general settings form/tab
     */
    public function showSettings(?PluginConfigForm $form = null) : void
    {
        if ($form === null) {
            $form = new PluginConfigForm();
            $form->setValuesByArray(
                $this->plugin->getPluginConfig()->toArray(["matrixAdminPassword"]),
                true
            );
        }

        $this->mainTpl->setContent($form->getHTML());
    }

    public function saveSettings() : void
    {
        $form = new PluginConfigForm();

        if (!$form->checkInput()) {
            ilUtil::sendFailure($this->plugin->txt("general.update.failed"));
            $form->setValuesByPost();
            $this->showSettings($form);
            return;
        }

        $form->setValuesByPost();

        $matrixServerUrl = rtrim($form->getInput("matrixServerUrl"), "/");

        $this->plugin->getPluginConfig()
                     ->setMatrixServerUrl($matrixServerUrl)
                     ->setMatrixAdminUsername($form->getInput("matrixAdminUsername"))
                     ->setChatInitialLoadLimit((int) $form->getInput("chatInitialLoadLimit"))
                     ->setChatHistoryLoadLimit((int) $form->getInput("chatHistoryLoadLimit"))
                     ->setLoginMethod($form->getInput("loginMethod"))
                     ->setUseLdapAutoLogin((bool) $form->getInput("useLdapAutoLogin"));

        $matrixAdminPassword = $form->getInput("matrixAdminPassword");
        if ($matrixAdminPassword !== "") {
            $this->plugin->getPluginConfig()->setMatrixAdminPassword($matrixAdminPassword);
        }

        try {
            $this->plugin->getPluginConfig()->save();
            ilUtil::sendSuccess($this->plugin->txt("general.update.success"), true);
        } catch (Exception $e) {
            ilUtil::sendFailure($this->plugin->txt($e->getMessage()), true);
        }
        $this->ctrl->redirectByClass(self::class, "showSettings");
    }

    /**
     * Calls the function for a received command
     *
     * @param $cmd
     * @throws Exception
     */
    public function performCommand($cmd)
    {
        $cmd = $cmd === "configure" ? $this->getDefaultCommand() : $cmd;

        if (method_exists($this, $cmd)) {
            $this->{$cmd}();
        } else {
            ilUtil::sendFailure(sprintf($this->plugin->txt("general.cmd.notFound"), $cmd));
            $this->{$this->getDefaultCommand()}();
        }
    }

    /**
     * Returns the default command
     *
     * @return string
     */
    protected function getDefaultCommand() : string
    {
        return "showSettings";
    }
}
