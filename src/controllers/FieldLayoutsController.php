<?php
/**
 * Ad Wizard plugin for Craft CMS
 *
 * Easily manage custom advertisements on your website.
 *
 * @author    Double Secret Agency
 * @link      https://www.doublesecretagency.com/
 * @copyright Copyright (c) 2014 Double Secret Agency
 */

namespace doublesecretagency\adwizard\controllers;

use Craft;
use craft\errors\MissingComponentException;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use doublesecretagency\adwizard\AdWizard;
use doublesecretagency\adwizard\elements\Ad;
use doublesecretagency\adwizard\models\FieldLayout;
use Throwable;
use yii\base\Exception;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

/**
 * Class FieldLayoutsController
 * @since 2.1.0
 */
class FieldLayoutsController extends Controller
{

    /**
     * Called before displaying the field layouts page.
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        $this->requireLogin();

        $fieldLayouts = AdWizard::$plugin->fieldLayouts->getFieldLayouts();

        return $this->renderTemplate('ad-wizard/fieldlayouts', [
            'crumbs' => $this->_fieldLayoutsCrumbs(),
            'selectedSubnavItem' => 'fieldlayouts',
            'fullPageForm' => true,
            'fieldLayouts' => $fieldLayouts,
        ]);
    }

    /**
     * Edit a field layout.
     *
     * @param int|null $fieldLayoutId The field layout’s ID, if any.
     * @param FieldLayout|null $fieldLayout The field layout being edited, if there were any validation errors.
     * @return Response
     * @throws HttpException if the requested field layout cannot be found
     */
    public function actionEditFieldLayout(?int $fieldLayoutId = null, ?FieldLayout $fieldLayout = null): Response
    {
        $this->requireLogin();

        if ($fieldLayoutId !== null && !$fieldLayout) {
            $fieldLayout = AdWizard::$plugin->fieldLayouts->getLayoutById($fieldLayoutId);

            if (!$fieldLayout) {
                throw new HttpException('Field layout not found');
            }
        }

        if (!$fieldLayout) {
            $fieldLayout = new FieldLayout();
        }

        // Breadcrumbs
        $crumbs = $this->_fieldLayoutsCrumbs();

        // Append final crumb
        if ($fieldLayout->id) {
            $crumbs[] = [
                'label' => Craft::t('site', $fieldLayout->name),
                'url'   => UrlHelper::cpUrl('ad-wizard/fieldlayouts/'.$fieldLayout->id)
            ];
        } else {
            $crumbs[] = [
                'label' => Craft::t('ad-wizard', 'Create New Field Layout'),
                'url'   => UrlHelper::cpUrl('ad-wizard/fieldlayouts/new')
            ];
        }

        return $this->renderTemplate('ad-wizard/fieldlayouts/_edit', [
            'crumbs' => $crumbs,
            'selectedSubnavItem' => 'fieldlayouts',
            'fullPageForm' => true,
            'fieldLayoutId' => $fieldLayoutId,
            'fieldLayout' => $fieldLayout,
        ]);
    }

    /**
     * Save a field layout.
     *
     * @return Response|null
     * @throws BadRequestHttpException
     * @throws Throwable
     * @throws MissingComponentException
     * @throws Exception
     */
    public function actionSaveFieldLayout(): ?Response
    {
        $this->requirePostRequest();
        $this->requireLogin();

        // Set the field layout
        $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
        $fieldLayout->type = Ad::class;
        $fieldLayout->reservedFieldHandles = [
            'url',
            'adGraphic'
        ];

        if (!Craft::$app->getFields()->saveLayout($fieldLayout)) {
            Craft::$app->getUrlManager()->setRouteParams([
                'variables' => [
                    'fieldLayout' => $fieldLayout,
                ],
            ]);
            $this->setFailFlash(Craft::t('ad-wizard', 'Couldn’t save field layout.'));
            return null;
        }

        // Get specified layout name
        $name = Craft::$app->getRequest()->getBodyParam('name');

        // Create relationship to Ad Wizard
        AdWizard::$plugin->fieldLayouts->saveLayout($fieldLayout, $name);

        $this->setSuccessFlash(Craft::t('ad-wizard', 'Field layout saved.'));
        return $this->redirectToPostedUrl();
    }

    /**
     * Deletes a field layout.
     *
     * @return Response
     * @throws Throwable
     * @throws BadRequestHttpException
     */
    public function actionDeleteFieldLayout(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $this->requireLogin();

        $fieldLayoutId = Craft::$app->getRequest()->getRequiredBodyParam('id');

        AdWizard::$plugin->fieldLayouts->deleteLayoutById($fieldLayoutId);

        return $this->asJson(['success' => true]);
    }

    // ========================================================================= //

    /**
     * Breadcrumbs for field layout pages.
     *
     * @return array
     */
    private function _fieldLayoutsCrumbs(): array
    {
        return [
            [
                'label' => Craft::t('ad-wizard', 'Ad Wizard'),
                'url'   => UrlHelper::cpUrl('ad-wizard')
            ],
            [
                'label' => Craft::t('ad-wizard', 'Field Layouts'),
                'url'   => UrlHelper::cpUrl('ad-wizard/fieldlayouts')
            ]
        ];
    }

}
