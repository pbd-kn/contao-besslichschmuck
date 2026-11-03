<?php

declare(strict_types=1);
namespace Pbdkn\ContaoBesslichschmuck\Backend;
use Contao\BackendModule;
use Contao\BackendUser;
use Contao\System;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Pbdkn\ContaoBesslichschmuck\Util\PriceSourceSwitch;
use Symfony\Component\Security\Csrf\CsrfToken;
final class PriceSourceBackend extends BackendModule
{
    protected $strTemplate='be_heike_price_source';
    protected function compile():void
    {
        if(!BackendUser::getInstance()->isAdmin){throw new AccessDeniedException('Nur Administratoren dürfen die Preisquelle umstellen.');}
        $container=System::getContainer();$request=$container->get('request_stack')->getCurrentRequest();$session=$request->getSession();
        $csrf=$container->get('contao.csrf.token_manager');$switch=new PriceSourceSwitch($container->get('database_connection'));
        $preview=null;$error=$message='';
        if($request->isMethod('POST')&&$request->request->get('FORM_SUBMIT')==='heike_price_source') {
            $token=$request->request->get('REQUEST_TOKEN');
            if(!is_string($token)||!$csrf->isTokenValid(new CsrfToken($container->getParameter('contao.csrf_token_name'),$token))){throw new AccessDeniedException('Ungültiges Anfragetoken.');}
            try {
                $action=$request->request->get('action');
                if($action==='activate') {
                    $saved=$session->get('heike_source_preview');
                    if(!is_array($saved)||$request->request->get('confirmed')!=='1'||!hash_equals($saved['nonce'],(string)$request->request->get('nonce'))){throw new \RuntimeException('Bitte zuerst den Vergleich prüfen und bestätigen.');}
                    $switch->activate($saved['plan']);$session->remove('heike_source_preview');$message='Tabellenverwaltung aktiviert. Die ursprüngliche Preisliste bleibt erhalten.';
                } elseif($action==='legacy') {
                    if($request->request->get('confirmed')!=='1'){throw new \RuntimeException('Bitte die Rückkehr zur ursprünglichen Preisliste bestätigen.');}
                    $switch->useLegacy();$session->remove('heike_source_preview');$message='Die Website verwendet wieder die ursprüngliche Preisliste.';
                } else {
                    $session->remove('heike_source_preview');$preview=$switch->preview();$nonce=bin2hex(random_bytes(16));$session->set('heike_source_preview',['nonce'=>$nonce,'plan'=>$preview]);
                }
            }catch(\Throwable $e){$error=$e->getMessage();$session->remove('heike_source_preview');}
        }
        $this->Template->status=$switch->status();$this->Template->preview=$preview;$this->Template->error=$error;$this->Template->message=$message;$this->Template->nonce=$nonce??'';$this->Template->token=$csrf->getDefaultTokenValue();
    }
}
