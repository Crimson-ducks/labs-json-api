<?php
/**
 * Created by PhpStorm.
 * User: dan
 * Date: 27/08/15
 * Time: 09:52
 */

namespace Clearbooks\LabsApi\Toggle;


use Clearbooks\Dilex\JwtGuard\IdentityProvider;
use Clearbooks\Labs\Toggle\GetActivatedToggles;
use Clearbooks\Dilex\Endpoint;
use Clearbooks\Labs\Toggle\Object\GetActivatedTogglesRequest;
use Clearbooks\LabsApi\User\Group;
use Clearbooks\LabsApi\User\RawSegmentDataToSegmentObjectConverter;
use Clearbooks\LabsApi\User\User;
use Clearbooks\LabsMysql\Toggle\Entity\Toggle;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GetTogglesActivatedByUser implements Endpoint
{
    /**
     * @var GetActivatedToggles
     */
    private $getActivatedToggles;

    /**
     * @var IdentityProvider
     */
    private $identityProvider;

    /**
     * @var RawSegmentDataToSegmentObjectConverter
     */
    private $rawSegmentDataToSegmentObjectConverter;

    /**
     * GetTogglesActivatedByUser constructor.
     * @param GetActivatedToggles $getActivatedToggles
     * @param IdentityProvider $identityProvider
     * @param RawSegmentDataToSegmentObjectConverter $rawSegmentDataToSegmentObjectConverter
     */
    public function __construct( GetActivatedToggles $getActivatedToggles, IdentityProvider $identityProvider,
                                 RawSegmentDataToSegmentObjectConverter $rawSegmentDataToSegmentObjectConverter )
    {
        $this->getActivatedToggles = $getActivatedToggles;
        $this->identityProvider = $identityProvider;
        $this->rawSegmentDataToSegmentObjectConverter = $rawSegmentDataToSegmentObjectConverter;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function execute(Request $request)
    {
        $userId = $this->identityProvider->getUserId();
        if(!isset($userId)) {
            return new JsonResponse('Missing user identifier', 400);
        }

        $segments = $this->rawSegmentDataToSegmentObjectConverter->getSegmentObjects( $this->identityProvider->getSegments() );
        $request = new GetActivatedTogglesRequest( new User( $this->identityProvider ), new Group( $this->identityProvider ), $segments );
        $activatedToggles = $this->getActivatedToggles->execute( $request );
        $json = [];
        /**
         * @var Toggle $toggle
         */
        foreach($activatedToggles as $toggle) {
            $json[$toggle->getMarketingToggleTitle()] = 1;
        }
        return new JsonResponse($json);
    }
}