<?php

namespace Icinga\Module\Vspheredb\Controllers;

use Icinga\Module\Vspheredb\Web\Controller;
use Icinga\Module\Vspheredb\Web\Form\FilterVCenterForm;
use Icinga\Module\Vspheredb\Web\Table\PerformanceCounterTable;
use Icinga\Module\Vspheredb\Web\Tabs\VCenterTabs;
use Icinga\Module\Vspheredb\Web\Widget\AdditionalTableActions;
use ipl\Html\Html;

class PerfdataController extends Controller
{
    /**
     * @throws \Icinga\Security\SecurityException
     */
    public function init()
    {
        parent::init();
        $this->assertPermission('vspheredb/admin');
    }

    public function indexAction()
    {
        $vCenter = $this->requireVCenter();
        $this->addTitle($this->translate('Performance Data'));
        $this->tabs(new VCenterTabs($vCenter))->activate('perfdata');
        $this->content()->add(Html::tag('p', $this->translate(
            'This module can collect Performance Data from your vCenters or ESXi Hosts.'
            . ' Different on '
        )));
    }

    public function countersAction()
    {
        $vCenter = $this->requireVCenter();
        $this->tabs(new VCenterTabs($vCenter))->activate('perfcounters');
        $this->addTitle($this->translate('Available Performance Counters'));
        $form = new FilterVCenterForm($this->db());
        $form->handleRequest($this->getServerRequest());
        $this->content()->add(Html::tag('div', ['class' => 'icinga-module module-director'], $form));
        $uuid = $form->getHexUuid();
        if ($uuid === null) {
            return;
        }
        $table = (new PerformanceCounterTable($this->db(), $this->url(), $vCenter));
        (new AdditionalTableActions($table, $this->Auth(), $this->url()))
            ->appendTo($this->actions());
        $table->renderTo($this);
    }

    protected function handleTabs()
    {
        $action = $this->getRequest()->getActionName();
        $this->tabs()->add('index', [
            'label' => $this->translate('Performance Data'),
            'url'   => 'vspheredb/perfdata',
        ])->add('counters', [
            'label' => $this->translate('Counters'),
            'url'   => 'vspheredb/perfdata/counters',
        ])->activate($action);
    }
}
