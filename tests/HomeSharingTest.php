<?php

use App\Controllers\Device;
use App\Controllers\Home;
use App\Controllers\Homes;
use App\Controllers\Room;
use App\Controllers\Sharing;
use App\Libraries\HomeAccess;
use App\Libraries\HomeInvitations;
use App\Libraries\Mailer;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use Config\Database;

final class HomeSharingTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    private static BaseConnection $server;
    private static array $databaseSettings;
    private static string $databaseName;
    private static bool $schemaCreated = false;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $settings = config('Database')->default;
        $settings['database'] = '';
        $settings['DBPrefix'] = '';
        self::$server = Database::connect($settings, false);
        self::$databaseName = 'coolhome_test_' . bin2hex(random_bytes(6));
        self::$server->query('CREATE DATABASE `' . self::$databaseName . '` CHARACTER SET utf8mb4');
        $settings['database'] = self::$databaseName;
        self::$databaseSettings = $settings;
    }

    public static function tearDownAfterClass(): void
    {
        self::$server->query('DROP DATABASE `' . self::$databaseName . '`');
        self::$server->close();
        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        parent::setUp();
        config('Database')->tests = self::$databaseSettings;
        $this->db = Database::connect('tests');
        if (! self::$schemaCreated) {
            $this->db->query('CREATE TABLE t_user (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, email VARCHAR(254), password VARCHAR(255), nom VARCHAR(100), prenom VARCHAR(100), actif INT, created DATETIME)');
            $this->db->query('CREATE TABLE t_home (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, owner INT, title VARCHAR(100), mode INT, confort_temp FLOAT, absence_temp FLOAT, location VARCHAR(255))');
            $this->db->query('CREATE TABLE t_room (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, home INT, title VARCHAR(100), mode INT)');
            $this->db->query('CREATE TABLE t_room_program (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, room INT, start TIME, weekday INT, temp FLOAT)');
            $this->db->query('CREATE TABLE t_device (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, owner INT, room INT NULL, uuid VARCHAR(100), title VARCHAR(100), kind VARCHAR(30), temp FLOAT, reference INT)');
            $this->db->query('CREATE TABLE t_data (room INT, device INT, sensor VARCHAR(100), kind VARCHAR(30), value FLOAT, created DATETIME)');
            require_once APPPATH . 'Database/Migrations/2026-10-29-100000_AddHomeSharing.php';
            $migration = new \App\Database\Migrations\AddHomeSharing(Database::forge('tests'));
            $migration->up();
            $migration->down();
            $migration->up();
            self::$schemaCreated = true;
        }
        foreach (['t_home_member', 't_home_invitation', 't_data', 't_device', 't_room_program', 't_room', 't_home', 't_user'] as $table) {
            $this->db->table($table)->truncate();
        }
        foreach ([1 => 'owner@example.com', 2 => 'resident@example.com', 3 => 'coowner@example.com', 4 => 'other@example.com'] as $id => $email) {
            $this->db->table('t_user')->insert(['id' => $id, 'email' => $email, 'actif' => 1]);
        }
        $this->db->table('t_home')->insert(['id' => 1, 'owner' => 1, 'title' => 'Maison', 'mode' => 0, 'confort_temp' => 20, 'absence_temp' => 16, 'location' => '']);
        $this->db->table('t_home')->insert(['id' => 2, 'owner' => 4, 'title' => 'Privee', 'mode' => 0]);
        $this->db->table('t_home_member')->insertBatch([
            ['home' => 1, 'user' => 2, 'role' => 'resident'],
            ['home' => 1, 'user' => 3, 'role' => 'owner'],
        ]);
        $this->db->table('t_room')->insert(['id' => 1, 'home' => 1, 'title' => 'Salon', 'mode' => 0]);
        $this->db->table('t_room')->insert(['id' => 2, 'home' => 2, 'title' => 'Privee', 'mode' => 0]);
        $this->db->table('t_device')->insert(['id' => 1, 'owner' => 2, 'room' => 1, 'title' => 'Thermostat', 'uuid' => 'test']);
    }

    private function asUser(int $id, array $post = []): void
    {
        service('session')->set('user', $this->db->table('t_user')->where('id', $id)->get()->getRowArray());
        $this->request->setMethod($post === [] ? 'GET' : 'POST');
        $this->request->setGlobal('post', $post);
    }

    private function invitation(string $email = 'new@example.com', string $role = 'resident', int $expiresIn = 3600): string
    {
        $token = bin2hex(random_bytes(32));
        $this->db->table('t_home_invitation')->insert([
            'home' => 1, 'email' => $email, 'role' => $role,
            'token_hash' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', time() + $expiresIn),
        ]);
        return $token;
    }

    public function testRolesAndVisibility(): void
    {
        $this->assertSame('owner', HomeAccess::role(1, 1));
        $this->assertSame('resident', HomeAccess::role(1, 2));
        $this->assertSame('owner', HomeAccess::role(1, 3));
        $this->assertNull(HomeAccess::role(1, 4));
        foreach ([1, 2, 3] as $user) {
            $rows = $this->db->query('SELECT h.id FROM t_home h WHERE ' . HomeAccess::condition($user))->getResultArray();
            $this->assertSame([['id' => '1']], $rows);
        }
        $this->asUser(2);
        $result = $this->controller(Homes::class)->execute('index');
        $result->assertSee('Habitant');
        $result->assertDontSee('Privee');
        $this->assertSame(2, $this->db->table('t_home')->countAllResults());
    }

    public function testResidentCanChangeOnlyHomeTemperaturesAndMode(): void
    {
        $this->asUser(2, ['action' => 'UpdateHome', 'title' => 'Hacked', 'location' => 'Hacked', 'mode' => 1, 'confort_temp' => 21.5, 'absence_temp' => 15]);
        $this->controller(Home::class)->execute('index', 1)->assertRedirect();
        $home = $this->db->table('t_home')->where('id', 1)->get()->getRowArray();
        $this->assertSame('Maison', $home['title']);
        $this->assertSame('', $home['location']);
        $this->assertSame(21.5, (float) $home['confort_temp']);
        $this->assertSame(1, (int) $home['mode']);
    }

    public function testResidentCannotDeleteOrCreateStructureOrManageSharing(): void
    {
        $this->asUser(2, ['action' => 'DeleteRoom']);
        $this->controller(Room::class)->execute('index', 1)->assertStatus(403);
        $this->asUser(2);
        $this->controller(Homes::class)->execute('remove', 1)->assertRedirect();
        $this->controller(Home::class)->execute('addroom', 1)->assertRedirect();
        $this->controller(Sharing::class)->execute('index', 1)->assertStatus(403);
        $this->assertSame(2, $this->db->table('t_home')->countAllResults());
        $this->assertSame(2, $this->db->table('t_room')->countAllResults());
    }

    public function testResidentCanChangeRoomModeAndProgramsButNotItsName(): void
    {
        $this->asUser(2, ['action' => 'UpdateRoom', 'title' => 'Hacked', 'mode' => -1]);
        $this->controller(Room::class)->execute('index', 1)->assertRedirect();
        $room = $this->db->table('t_room')->where('id', 1)->get()->getRowArray();
        $this->assertSame('Salon', $room['title']);
        $this->assertSame(-1, (int) $room['mode']);
        $this->asUser(2, ['action' => 'AddProgram', 'new_start' => '08:30', 'new_weekday' => [1, 3], 'new_temp' => 22]);
        $this->controller(Room::class)->execute('index', 1)->assertRedirect();
        $program = $this->db->table('t_room_program')->get()->getRowArray();
        $this->assertSame('08:30:00', $program['start']);
        $this->assertSame(22.0, (float) $program['temp']);
    }

    public function testCoownerCanManageHouseAndAssignedDeviceButCannotUseForeignRoom(): void
    {
        $this->asUser(3, ['action' => 'UpdateDevice', 'room' => 2, 'title' => 'Bad']);
        $this->controller(Device::class)->execute('index', 1)->assertStatus(403);
        $this->asUser(3, ['action' => 'UpdateDevice', 'room' => 1, 'title' => 'Updated', 'kind' => 'HEATER']);
        $this->controller(Device::class)->execute('index', 1)->assertRedirect();
        $this->assertSame('Updated', $this->db->table('t_device')->get()->getRowArray()['title']);
        $this->asUser(3);
        $this->controller(Home::class)->execute('addroom', 1)->assertRedirect();
        $this->assertSame(3, $this->db->table('t_room')->countAllResults());
    }

    public function testResidentCannotAdministerEvenTheirOwnAssignedDevice(): void
    {
        $this->asUser(2, ['action' => 'DeleteDevice']);
        $this->controller(Device::class)->execute('index', 1)->assertRedirectTo('/devices');
        $this->assertSame(1, $this->db->table('t_device')->countAllResults());
        $this->controller(Device::class)->execute('datasfordt', 1)->assertStatus(403);
    }

    public function testChartsAndViewsAreSharedButNotPublic(): void
    {
        $this->asUser(2);
        $this->controller(Home::class)->execute('datachart', 1)->assertOK();
        $this->controller(Room::class)->execute('datachart', 1)->assertOK();
        $home = $this->controller(Home::class)->execute('index', 1);
        $home->assertSee('Habitant');
        $home->assertDontSee('Ajouter une piece');
        $home->assertDontSee('Supprimer cette maison');
        $home->assertSee('Partage de la maison');
        $home->assertDontSee('Partager / gerer les membres');
        $room = $this->controller(Room::class)->execute('index', 1);
        $room->assertDontSee('Supprimer cette piece');
        $room->assertSee('Enregistrer les programmes');
        $this->asUser(4);
        $this->controller(Home::class)->execute('datachart', 1)->assertStatus(404);
        $this->controller(Room::class)->execute('datachart', 1)->assertStatus(404);
    }

    public function testSharingCardIsBetweenRoomsAndDeletionForOwners(): void
    {
        $this->asUser(1);
        $result = $this->controller(Home::class)->execute('index', 1);
        $result->assertSee('Partager / gerer les membres');
        $html = $result->getBody();
        $rooms = strpos($html, 'Ajouter une piece');
        $sharing = strpos($html, 'Partage de la maison');
        $deletion = strpos($html, 'Zone de suppression');
        $this->assertNotFalse($rooms);
        $this->assertNotFalse($sharing);
        $this->assertNotFalse($deletion);
        $this->assertTrue($rooms < $sharing && $sharing < $deletion);
    }

    public function testInvitationCreatesAccountAndIsSingleUse(): void
    {
        $token = $this->invitation();
        $service = new HomeInvitations();
        $this->assertNotNull($service->find($token));
        $result = $service->accept($token, null, 'strong-password');
        $this->assertSame(1, $result['home']);
        $this->assertSame('new@example.com', $result['user']['email']);
        $this->assertArrayNotHasKey('password', $result['user']);
        $user = $this->db->table('t_user')->where('email', 'new@example.com')->get()->getRowArray();
        $this->assertTrue(password_verify('strong-password', $user['password']));
        $this->assertSame('resident', HomeAccess::role(1, (int) $user['id']));
        $this->assertNull($service->find($token));
        $this->expectException(DomainException::class);
        $service->accept($token, (int) $user['id']);
    }

    public function testInvitationPageCreatesAndLogsInNewAccount(): void
    {
        $token = $this->invitation();
        service('session')->remove('user');
        $this->request->setMethod('GET');
        $this->controller(Sharing::class)->execute('invitation', $token)->assertSee('Creer votre compte');
        $this->request->setMethod('POST');
        $this->request->setGlobal('post', ['password' => 'strong-password', 'password_confirm' => 'strong-password']);
        $this->controller(Sharing::class)->execute('invitation', $token)->assertRedirectTo('/home/index/1');
        $this->assertSame('new@example.com', service('session')->get('user')['email']);
    }

    public function testExistingAccountInvitationLinksBackToLogin(): void
    {
        $token = $this->invitation('other@example.com');
        service('session')->remove('user');
        $this->request->setMethod('GET');
        $result = $this->controller(Sharing::class)->execute('invitation', $token);
        $result->assertSee('Se connecter pour accepter');
        $this->assertStringContainsString('backto=' . rawurlencode('/invitation/' . $token), $result->getBody());
        $this->asUser(4);
        $result = $this->controller(Sharing::class)->execute('invitation', $token);
        $result->assertSee("Accepter l'invitation");
    }

    public function testManualLinkIsShownAndEscapedInSharingView(): void
    {
        $url = 'https://coolhome.example/invitation/' . str_repeat('a', 64);
        $html = view('home/sharing', [
            'home' => ['id' => 1, 'title' => 'Maison'],
            'owner' => ['email' => 'owner@example.com'],
            'members' => [],
            'invitations' => [],
            'message' => 'Invitation creee, aucun email envoye.',
            'error' => null,
            'invitationUrl' => $url,
        ]);
        $this->assertStringContainsString('value="' . esc($url, 'attr') . '"', $html);
        $this->assertStringContainsString('Copier le lien', $html);
        $this->assertStringContainsString('aucun email envoye', $html);
        $this->assertStringContainsString('navigator.clipboard.writeText', $html);
    }

    public function testInvitationRequiresMatchingExistingAccount(): void
    {
        $token = $this->invitation('other@example.com', 'owner');
        $service = new HomeInvitations();
        try {
            $service->accept($token, 2);
            $this->fail('A different email must not accept.');
        } catch (DomainException) {
            $this->assertNotNull($service->find($token));
        }
        try {
            $service->accept($token, null, 'replacement-password');
            $this->fail('Existing accounts must log in.');
        } catch (DomainException) {
            $this->assertSame(4, $this->db->table('t_user')->countAllResults());
        }
        $service->accept($token, 4);
        $this->assertSame('owner', HomeAccess::role(1, 4));
    }

    public function testExpiredAndCancelledInvitationsCannotBeAccepted(): void
    {
        $token = $this->invitation('expired@example.com', 'resident', -1);
        $service = new HomeInvitations();
        $this->assertNull($service->find($token));
        try {
            $service->accept($token, null, 'strong-password');
            $this->fail('Expired invitations must fail.');
        } catch (DomainException) {
            $this->assertSame(4, $this->db->table('t_user')->countAllResults());
        }
        $token = $this->invitation();
        $this->asUser(3, ['action' => 'CancelInvitation', 'invitation_id' => $this->db->insertID()]);
        $this->controller(Sharing::class)->execute('index', 1)->assertSee('Invitation annulee.');
        $this->assertNull($service->find($token));
    }

    public function testSendingReplacesPreviousLinkAndUsesRequestedRole(): void
    {
        $old = $this->invitation();
        $mailer = $this->createMock(Mailer::class);
        $body = '';
        $mailer->expects($this->once())->method('send')->willReturnCallback(static function (string $email, string $subject, string $message) use (&$body): bool {
            self::assertSame('new@example.com', $email);
            $body = $message;
            return true;
        });
        $service = new HomeInvitations($this->db, $mailer);
        $service->send(1, 3, ' NEW@example.com ', 'owner');
        $this->assertNull($service->find($old));
        $this->assertSame(1, preg_match('~/invitation/([a-f0-9]{64})~', $body, $match));
        $this->assertSame('owner', $service->find($match[1])['role']);
        $record = $this->db->table('t_home_invitation')->get()->getRowArray();
        $this->assertNotSame($match[1], $record['token_hash']);
        $this->assertEqualsWithDelta(time() + 7 * DAY, strtotime($record['expires_at']), 5);
    }

    public function testFailedEmailStillProvidesCopyableInvitation(): void
    {
        $old = $this->invitation();
        $mailer = $this->createMock(Mailer::class);
        $mailer->method('send')->willReturn(false);
        $service = new HomeInvitations($this->db, $mailer);
        $result = $service->send(1, 1, 'new@example.com', 'owner');
        $this->assertFalse($result['emailSent']);
        $this->assertNull($service->find($old));
        $this->assertSame(1, preg_match('~/invitation/([a-f0-9]{64})~', $result['url'], $match));
        $this->assertNotNull($service->find($match[1]));
        $service->accept($match[1], null, 'strong-password');
        $this->assertSame(5, $this->db->table('t_user')->countAllResults());
    }

    public function testOnlyOwnersCanInvite(): void
    {
        $mailer = $this->createMock(Mailer::class);
        $mailer->expects($this->never())->method('send');
        $this->expectException(DomainException::class);
        (new HomeInvitations($this->db, $mailer))->send(1, 2, 'new@example.com', 'owner');
    }

    public function testRoleChangesAndRevocationApplyImmediately(): void
    {
        $this->asUser(3, ['action' => 'UpdateMember', 'user_id' => 2, 'role' => 'owner']);
        $this->controller(Sharing::class)->execute('index', 1)->assertSee('Role mis a jour.');
        $this->assertSame('owner', HomeAccess::role(1, 2));
        $this->asUser(3, ['action' => 'RemoveMember', 'user_id' => 2]);
        $this->controller(Sharing::class)->execute('index', 1)->assertSee('Acces retire.');
        $this->assertNull(HomeAccess::role(1, 2));
        $this->asUser(2);
        $this->controller(Home::class)->execute('datachart', 1)->assertStatus(404);
    }

    public function testInitialOwnerCannotBeRemovedAndHomeDeletionClearsSharing(): void
    {
        $this->asUser(3, ['action' => 'RemoveMember', 'user_id' => 1]);
        $this->controller(Sharing::class)->execute('index', 1)->assertSee('Owner initial conserve');
        $this->assertSame('owner', HomeAccess::role(1, 1));
        $token = $this->invitation();
        $this->asUser(3);
        $this->controller(Homes::class)->execute('remove', 1)->assertRedirect();
        $this->assertSame(0, $this->db->table('t_home_member')->countAllResults());
        $this->assertNull((new HomeInvitations())->find($token));
        $this->assertSame(1, $this->db->table('t_room')->countAllResults());
        $this->assertNull($this->db->table('t_device')->get()->getRowArray()['room']);
        $this->assertNotEmpty($this->db->query('SELECT d.id FROM t_device d WHERE ' . HomeAccess::deviceCondition(2))->getResultArray());
    }
}
