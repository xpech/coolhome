<?php

namespace App\Controllers;

use App\Libraries\HomeAccess;
use App\Libraries\HomeInvitations;
use DomainException;

class Sharing extends BaseController
{
    public function index(int $id)
    {
        $auth = $this->requireUser('/home/sharing/' . $id);
        if ($auth instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $auth;
        }
        if (HomeAccess::role($id, (int) $auth['id']) !== HomeAccess::OWNER) {
            return $this->response->setStatusCode(403)->setBody('Action reservee aux Owners.');
        }
        $this->response->setHeader('Cache-Control', 'no-store');

        $db = db_connect();
        $home = $db->table('t_home')->where('id', $id)->get()->getRowArray();
        $error = null;
        $message = null;
        $invitationUrl = null;
        if ($this->request->getMethod(true) === 'POST') {
            try {
                $action = (string) $this->request->getPost('action');
                if ($action === 'Invite') {
                    $result = (new HomeInvitations())->send(
                        $id,
                        (int) $auth['id'],
                        (string) $this->request->getPost('email'),
                        (string) $this->request->getPost('role')
                    );
                    $invitationUrl = $result['url'];
                    $message = $result['emailSent']
                        ? 'Invitation envoyee par email. Vous pouvez aussi copier le lien ci-dessous.'
                        : 'Invitation creee, mais aucun email n\'a ete envoye (SMTP absent ou envoi impossible). Copiez le lien ci-dessous pour le transmettre.';
                } elseif ($action === 'CancelInvitation') {
                    $db->table('t_home_invitation')->where(['home' => $id, 'id' => (int) $this->request->getPost('invitation_id')])->delete();
                    if ($db->affectedRows() !== 1) {
                        throw new DomainException('Invitation introuvable.');
                    }
                    $message = 'Invitation annulee.';
                } elseif (in_array($action, ['UpdateMember', 'RemoveMember'], true)) {
                    $userId = (int) $this->request->getPost('user_id');
                    if ($userId === (int) $home['owner']) {
                        throw new DomainException('L\'Owner initial conserve son acces a la maison.');
                    }
                    $where = ['home' => $id, 'user' => $userId];
                    if ($db->table('t_home_member')->where($where)->countAllResults() === 0) {
                        throw new DomainException('Membre introuvable.');
                    }
                    if ($action === 'RemoveMember') {
                        $db->table('t_home_member')->where($where)->delete();
                        $message = 'Acces retire.';
                    } else {
                        $role = (string) $this->request->getPost('role');
                        if (! HomeAccess::validRole($role)) {
                            throw new DomainException('Role invalide.');
                        }
                        $db->table('t_home_member')->where($where)->update(['role' => $role]);
                        $message = 'Role mis a jour.';
                    }
                    if ($userId === (int) $auth['id']) {
                        return redirect()->to('/home/index/' . $id);
                    }
                } else {
                    throw new DomainException('Action invalide.');
                }
            } catch (DomainException $e) {
                $error = $e->getMessage();
            }
        }

        return view('home/sharing', [
            'home' => $home,
            'owner' => $db->table('t_user')->select('email')->where('id', $home['owner'])->get()->getRowArray(),
            'members' => $db->table('t_home_member m')->select('m.user,m.role,u.email')->join('t_user u', 'u.id = m.user')->where('m.home', $id)->get()->getResultArray(),
            'invitations' => $db->table('t_home_invitation')->where('home', $id)->get()->getResultArray(),
            'error' => $error,
            'message' => $message,
            'invitationUrl' => $invitationUrl,
        ]);
    }

    public function invitation(string $token)
    {
        $invitations = new HomeInvitations();
        $invitation = $invitations->find($token);
        $this->response->setHeader('Referrer-Policy', 'no-referrer')->setHeader('Cache-Control', 'no-store');
        if (! $invitation) {
            return $this->response->setStatusCode(410)->setBody('Invitation invalide, expiree, annulee ou deja utilisee.');
        }

        $auth = $this->session->get('user');
        $userId = is_array($auth) && ! empty($auth['id']) ? (int) $auth['id'] : null;
        $accountExists = db_connect()->table('t_user')->where('LOWER(email)', $invitation['email'])->countAllResults() > 0;
        $error = null;
        if ($this->request->getMethod(true) === 'POST') {
            try {
                $password = (string) $this->request->getPost('password');
                if ($userId === null && $password !== (string) $this->request->getPost('password_confirm')) {
                    throw new DomainException('Les mots de passe ne correspondent pas.');
                }
                $result = $invitations->accept($token, $userId, $password);
                $this->session->regenerate(true);
                $this->session->set('user', $result['user']);

                return redirect()->to('/home/index/' . $result['home']);
            } catch (DomainException $e) {
                $error = $e->getMessage();
            }
        }

        return view('home/invitation', [
            'invitation' => $invitation,
            'token' => $token,
            'auth' => $auth,
            'accountExists' => $accountExists,
            'error' => $error,
        ]);
    }
}
