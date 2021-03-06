<?php
declare(strict_types=1);

namespace Shlinkio\Shlink\Rest\Action\Tag;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Shlinkio\Shlink\Core\Exception\EntityDoesNotExistException;
use Shlinkio\Shlink\Core\Service\Tag\TagServiceInterface;
use Shlinkio\Shlink\Rest\Action\AbstractRestAction;
use Shlinkio\Shlink\Rest\Util\RestUtils;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\I18n\Translator\TranslatorInterface;

class UpdateTagAction extends AbstractRestAction
{
    /**
     * @var TagServiceInterface
     */
    private $tagService;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        TagServiceInterface $tagService,
        TranslatorInterface $translator,
        LoggerInterface $logger = null
    ) {
        parent::__construct($logger);
        $this->tagService = $tagService;
        $this->translator = $translator;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     * @throws \InvalidArgumentException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        if (! isset($body['oldName'], $body['newName'])) {
            return new JsonResponse([
                'error' => RestUtils::INVALID_ARGUMENT_ERROR,
                'message' => $this->translator->translate(
                    'You have to provide both \'oldName\' and \'newName\' params in order to properly rename the tag'
                ),
            ], self::STATUS_BAD_REQUEST);
        }

        try {
            $this->tagService->renameTag($body['oldName'], $body['newName']);
            return new EmptyResponse();
        } catch (EntityDoesNotExistException $e) {
            return new JsonResponse([
                'error' => RestUtils::NOT_FOUND_ERROR,
                'message' => sprintf(
                    $this->translator->translate('It wasn\'t possible to find a tag with name \'%s\''),
                    $body['oldName']
                ),
            ], self::STATUS_NOT_FOUND);
        }
    }
}
