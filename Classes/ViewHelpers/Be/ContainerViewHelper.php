<?php
/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * View helper which allows you to create extbase based modules in the style of TYPO3 default modules.
 * Note: This feature is experimental!
 *
 * = Examples =
 *
 * <code title="Simple">
 * <f:be.container>your module content</f:be.container>
 * </code>
 * <output>
 * "your module content" wrapped with propper head & body tags.
 * Default backend CSS styles and JavaScript will be included
 * </output>
 *
 * <code title="All options">
 * <f:be.container pageTitle="foo" enableJumpToUrl="false" enableClickMenu="false" loadPrototype="false" loadScriptaculous="false" scriptaculousModule="someModule,someOtherModule" loadExtJs="true" loadExtJsTheme="false" extJsAdapter="jQuery" enableExtJsDebug="true" addCssFile="{f:uri.resource(path:'styles/backend.css')}" addJsFile="{f:uri.resource(path:'scripts/main.js')}">your module content</f:be.container>
 * </code>
 * <output>
 * "your module content" wrapped with propper head & body tags.
 * Custom CSS file EXT:your_extension/Resources/Public/styles/backend.css and JavaScript file EXT:your_extension/Resources/Public/scripts/main.js will be loaded
 * </output>
 *
 * @author Bastian Waidelich <bastian@typo3.org>
 * @license http://www.gnu.org/copyleft/gpl.html
 */
class Tx_SlubEvents_ViewHelpers_Be_ContainerViewHelper extends Tx_Fluid_ViewHelpers_Be_AbstractBackendViewHelper {

	/**
	 * Render start page with template.php and pageTitle
	 *
	 * @param string  $pageTitle title tag of the module. Not required by default, as BE modules are shown in a frame
	 * @param boolean $enableJumpToUrl If TRUE, includes "jumpTpUrl" javascript function required by ActionMenu. Defaults to TRUE
	 * @param boolean $enableClickMenu If TRUE, loads clickmenu.js required by BE context menus. Defaults to TRUE
	 * @param boolean $loadPrototype specifies whether to load prototype library. Defaults to TRUE
	 * @param boolean $loadScriptaculous specifies whether to load scriptaculous libraries. Defaults to FALSE
	 * @param string  $scriptaculousModule additionales modules for scriptaculous
	 * @param boolean $loadExtJs specifies whether to load ExtJS library. Defaults to FALSE
	 * @param boolean $loadExtJsTheme whether to load ExtJS "grey" theme. Defaults to FALSE
	 * @param string  $extJsAdapter load alternative adapter (ext-base is default adapter)
	 * @param boolean $enableExtJsDebug if TRUE, debug version of ExtJS is loaded. Use this for development only
	 * @param string $addCssFile Custom CSS file to be loaded
	 * @param string $addJsFile Custom JavaScript file to be loaded
	 * @param boolean $addJsDatepicker Load tceforms and datepicker
	 * @return string
	 * @see template
	 * @see t3lib_PageRenderer
	 */
	public function render($pageTitle = '', $enableJumpToUrl = TRUE, $enableClickMenu = TRUE, $loadPrototype = TRUE, $loadScriptaculous = FALSE, $scriptaculousModule = '', $loadExtJs = FALSE, $loadExtJsTheme = TRUE, $extJsAdapter = '', $enableExtJsDebug = FALSE, $addCssFile = NULL, $addJsFile = NULL, $addJsDatepicker = NULL) {
		$doc = $this->getDocInstance();
		$pageRenderer = $doc->getPageRenderer();

		if ($enableJumpToUrl) {
			$doc->JScode .= '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
					' . $doc->redirectUrls() . '
				</script>
			';
		}
		if ($enableClickMenu) {
			$doc->loadJavascriptLib('js/clickmenu.js');
		}
		if ($loadPrototype) {
			$pageRenderer->loadPrototype();
		}
		if ($loadScriptaculous) {
			$pageRenderer->loadScriptaculous($scriptaculousModule);
		}
		if ($loadExtJs) {
			$pageRenderer->loadExtJS(true, $loadExtJsTheme, $extJsAdapter);
			if ($enableExtJsDebug) {
				$pageRenderer->enableExtJsDebug();
			}
		}
		if ($addCssFile !== NULL) {
			$pageRenderer->addCssFile($addCssFile);
		}
		if ($addJsFile !== NULL) {
			$pageRenderer->addJsFile($addJsFile);
		}
		if ($addJsDatepicker) {
			if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) <  '6002000') {
				// TYPO3 4.7 to 6.1
				$pageRenderer->addJsFile('../t3lib/js/extjs/tceforms.js');
				$pageRenderer->addJsFile('../t3lib/js/extjs/ux/Ext.ux.DateTimePicker.js');
			} else {
				// TYPO3 6.2
				$pageRenderer->addJsFile('../typo3/sysext/backend/Resources/Public/JavaScript/tceforms.js');
				$pageRenderer->addJsFile('../typo3/js/extjs/ux/Ext.ux.DateTimePicker.js');
			}
			// Define settings for Date Picker
			$typo3Settings = array(
					'datePickerUSmode' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat'] ? 1 : 0,
					'dateFormat'       => array('j-n-Y', 'G:i j-n-Y'),
					'dateFormatUS'     => array('n-j-Y', 'G:i n-j-Y'),
			);
			$pageRenderer->addInlineSettingArray('', $typo3Settings);
		}

		$output = $this->renderChildren();
		$output = $doc->startPage($pageTitle) . $output;
		$output .= $doc->endPage();
		return $output;
	}
}
?>
