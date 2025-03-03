<?php
namespace Univie\UniviePure\ViewHelpers;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

class LinkViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper
{

    /**
     * @var string
     */
    protected $tagName = 'a';

    protected $additionalParams;

    public function initializeArguments()
    {
        $this->registerUniversalTagAttributes();
        $this->registerTagAttribute('target', 'string', 'Target of link', FALSE);
        $this->registerTagAttribute('rel', 'string', 'Specifies the relationship between the current document and the linked document', FALSE);
        $this->registerArgument('additionalParams', 'array', 'The arguments to add', false);
        $this->registerArgument('addQueryString', 'Bool', 'add Query', false);
        $this->registerArgument('addQueryStringMethod', 'string', 'Query method', false);
    }


    /**
     * @param file $file
     * @return string
     */
    public function render()
    {
        $getParams = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET();
        $link = '<a href="?id=' . $getParams['id'] . '&L=' . $getParams['L'] . '&' . $this->arguments['additionalParams']['p'] . '[' . $this->arguments['additionalParams']['c'] . ']='  . $this->arguments['additionalParams']['n'] . '">' . $this->arguments['additionalParams']['n'] . '</a>';
        $addParams = [
            $this->arguments['additionalParams']['p'] . '[' . $this->arguments['additionalParams']['c'] . ']' => $this->arguments['additionalParams']['n']
        ];
        $uriBuilder = $this->controllerContext->getUriBuilder();
        $uri = $uriBuilder->reset()
            ->setTargetPageUid($pageUid)
            ->setTargetPageType($pageType)
            ->setNoCache($noCache)
            ->setUseCacheHash(!$noCacheHash)
            ->setSection($section)
            ->setLinkAccessRestrictedPages($linkAccessRestrictedPages)
            ->setArguments($addParams)
            ->setCreateAbsoluteUri($absolute)
            ->setAddQueryString($addQueryString)
            ->setAddQueryStringMethod('GET')
            ->build();
         if (strlen($uri)) {
            $this->tag->addAttribute('href', $uri);
            $this->tag->setContent($this->renderChildren());
            $result = $this->tag->render();
        } else {
            $result = $this->renderChildren();
        }
        return $result;
    }
}
?>
