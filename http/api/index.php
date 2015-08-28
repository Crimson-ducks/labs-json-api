<?php
use Clearbooks\LabsApi\Release\GetAllPublicReleases;
use Clearbooks\LabsApi\Toggle\GetIsToggleActive;
use Clearbooks\LabsApi\Toggle\GetGroupTogglesForRelease;
use Clearbooks\LabsApi\Toggle\GetTogglesForRelease;
use Clearbooks\LabsApi\Toggle\GetUserTogglesForRelease;
use Emarref\Jwt\Algorithm\Hs512;
use Emarref\Jwt\Encryption\Factory;
use Emarref\Jwt\Exception\VerificationException;
use Emarref\Jwt\Jwt;
use Emarref\Jwt\Token;
use Emarref\Jwt\Verification\Context;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Clearbooks\LabsApi\User\UserToggleStatusModifier;

/**
 * Swagger api information
 *
 * @SWG\Info(
 *  title="Labs json api",
 *  description="An api for labs",
 *  version="Early"
 * )
 * @SWG\Tag(
 *  name="toggles",
 *  description="Operations involving toggles"
 * )
 * @SWG\Tag(
 *  name="releases",
 *  description="Operations involving releases"
 * )
 */
require_once "../../vendor/autoload.php";
$app = new \Silex\Application();
$app['debug'] = true;

$cb = new \DI\ContainerBuilder();
$cb->useAutowiring( true );

$cb->addDefinitions( '../../config/mappings.php' );
$app['resolver'] = $app->share(function () use ( $app, $cb ) {
    return new \Clearbooks\LabsApi\Framework\ControllerResolver( $app, $cb->build() );
});

$app->before(function(Request $request, Application $app) {
    $jwt = new Jwt();

    $algorithm = new Hs512('{{ secret_key }}');

    if(!$algorithm instanceof Hs512) {die;}

    $encryption = Factory::create($algorithm);
    $context = new Context($encryption);
    $token = $jwt->deserialize("{{ where_to_get_key }}");

    try {
        return $jwt->verify($token, $context);
    } catch (VerificationException $e) {
        return $e;
    }
});

/**
 * @SWG\Get(
 *  path="/public-releases/list",
 *  summary="Get a list of the public releases",
 *  produces={"application/json"},
 *  tags={"releases"},
 *  @SWG\Response(
 *   response=200,
 *   description="List of public releases"
 *  )
 * )
 */
$app->get( 'public-releases/list', GetAllPublicReleases::class);

/**
 * @SWG\Get(
 *  path="/toggle/list",
 *  summary="Get a list of toggles for a particular release",
 *  produces={"application/json"},
 *  tags={"toggles"},
 *  @SWG\Response(
 *   response=200,
 *   description="List of toggles"
 *  ),
 *  @SWG\Parameter(
 *   name="release",
 *   description="The id of the release to get the toggles for",
 *   in="query",
 *   required=true,
 *   type="integer"
 *  )
 * )
 */
$app->get( 'toggle/list', GetTogglesForRelease::class );

/**
 * @SWG\Get(
 *  path="/toggle/is-active",
 *  summary="Find out if a toggle is active",
 *  produces={"application/json"},
 *  tags={"toggles"},
 *  @SWG\Response(
 *   response=200,
 *   description="The name of the toggle and if it is active"
 *  ),
 *  @SWG\Response(
 *   response=400,
 *   description="You didn't specify a toggle name."
 *  ),
 *  @SWG\Parameter(
 *   name="name",
 *   description="The name of the toggle to check",
 *   in="query",
 *   required=true,
 *   type="string"
 *  )
 * )
 */
$app->get( 'toggle/is-active', GetIsToggleActive::class);

/**
 * @SWG\Get(
 *  path="/toggle/user/list",
 *  summary="Get all user toggles for a given release",
 *  produces={"application/json"},
 *  tags={"toggles", "releases"},
 *  @SWG\Response(
 *   response=200,
 *   description="A list of the toggle names for the specified release"
 *  ),
 *  @SWG\Response(
 *   response=400,
 *   description="You didn't specify a release ID"
 *  ),
 *  @SWG\Parameter(
 *   name="release",
 *   description="The release ID to get toggles for",
 *   in="query",
 *   required=true,
 *   type="integer"
 *  )
 * )
 */
$app->get( 'toggle/user/list', GetUserTogglesForRelease::class);

/**
 * @SWG\Get(
 *  path="/toggle/group/list",
 *  summary="List all group toggles for a particular release",
 *  produces={"application/json"},
 *  tags={"toggles", "releases"},
 *  @SWG\Response(
 *   response=200,
 *   description="A list of group toggles"
 *  ),
 *  @SWG\Parameter(
 *   name="release",
 *   description="The id of the release to get the toggles for",
 *   in="query",
 *   required=true,
 *   type="integer"
 *  )
 * )
 */
$app->get( 'toggle/group/list', GetGroupTogglesForRelease::class);

$app->post('toggle/change-status', UserToggleStatusModifier::class);

$app->run();
