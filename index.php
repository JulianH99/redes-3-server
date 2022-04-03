<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require_once 'pop.php';

require __DIR__ . './vendor/autoload.php';

session_start();

$app = AppFactory::create();


$twig = Twig::create('templates', ['cache' => FALSE, 'debug' => TRUE]);
$twig->addExtension(new \Twig\Extension\DebugExtension());

// $mail = new PHPMailer(true);
// $mail->isSMTP();
// $mail->SMTPDebug = 4;
// $mail->Host = '192.168.56.101';
// $mail->SMTPAuth = TRUE;
// $mail->Username = 'usuario1';
// $mail->Password = 'usuario1';
// $mail->SMTPSecure = FALSE;
// $mail->SMTPAutoTLS = FALSE;
// $mail->Port = 25;

// $mail->setFrom('julian@redestres.udistrital.edu.co', 'Julian');
// $mail->addAddress('usuario1@redestres.udistrital.edu.co', 'Usuario 1');

// $mail->isHTML(true);                                  //Set email format to HTML
// $mail->Subject = 'Here is the subject';
// $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
// $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

// $mail->send();



$app->add(TwigMiddleware::create($app, $twig));

$app->get('/', function (Request $request, Response $response) {
    $view = Twig::fromRequest($request);

    return $view->render($response, 'login.html.twig');
});

$app->post('/login', function (Request $request, Response $response) {

    $body = $request->getParsedBody();

    if (empty($body['username']) || empty($body['password'])) {
        return Twig::fromRequest($request)
            ->render(
                $response,
                'login.html.twig',
                ['error' => 'Username and password are required']
            );
    }

    $_SESSION['username'] = $body['username'];
    $_SESSION['password'] = $body['password'];

    return $response->withHeader('Location', '/home')
        ->withStatus(302);
});

$app->get('/home', function (Request $request, Response $response) {
    $username = $_SESSION['username'];
    $password = $_SESSION['password'];

    $messages = open_mailbox($username, $password);

    return Twig::fromRequest($request)->render($response, 'home.html.twig', [
        'messages' => $messages,
        'user' => $username
    ]);
});

$app->get('/logout', function (Request $request, Response $response) {
    unset($_SESSION['username']);
    unset($_SESSION['password']);

    return $response->withHeader('Location', '/')
        ->withStatus(302);
});

$app->get('/new-email', function (Request $request, Response $response){
    $view = Twig::fromRequest($request);
    return $view->render($response, 'write.html.twig');
});

$app->post('/send-mail', function (Request $request, Response $response) {
    $body = $request->getParsedBody();

    if (empty($body['from']) || empty($body['subject']) || empty($body['message'])) {
        return Twig::fromRequest($request)
            ->render(
                $response,
                'login.html.twig',
                ['error' => 'All fields in form are required']
            );
    }
    // Email Configuration
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPDebug = 4;
    $mail->Host = '192.168.56.101';
    $mail->SMTPAuth = TRUE;
    $mail->Username = $_SESSION['username'];
    $mail->Password = $_SESSION['password'];
    $mail->SMTPSecure = FALSE;
    $mail->SMTPAutoTLS = FALSE;
    $mail->Port = 25;
    $mail->setFrom($_SESSION['username'] . '@redestres.udistrital.edu.co', $_SESSION['username']);

    $emails = explode(',', $body['from']);
    foreach ($emails as $email){
        $mail->addAddress($email, '');
    }
    $mail->isHTML(true);
    $mail->Subject = $body['subject'];
    $mail->Body    = $body['message'];
    $mail->send();

    return $response->withHeader('Location', '/home')->withStatus(302);
});

$app->run();
