<?php

namespace App\Services;

use App\Models\JwtToken;
use App\Models\User;
use DateTimeImmutable;
use Exception;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint\SignedWith;

class JwtService
{
    protected Configuration $config;

    public function __construct()
    {

        $passPhrase = config('jwt.passphrase');
        $this->config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::file(storage_path('oauth-private.key'), $passPhrase),
            InMemory::file(storage_path('oauth-public.key'))
        );
    }

    public function generateToken(User $user, string $tokenTitle = null, array $restrictions = null, array $permissions = null): Plain
    {
        $token = $this->buildToken($user);

        JwtToken::create([
            'user_id' => $user->id,
            'unique_id' => $token->claims()->get('user_uuid'),
            'token_title' => $tokenTitle,
            'restrictions' => $restrictions,
            'permissions' => $permissions,
            'expires_at' => $token->claims()->get('exp'),
        ]);

        return $token;
    }

    public function parseToken(string $token): Plain
    {
        return $this->config->parser()->parse($token);
    }

    public function validateToken(Plain $token): bool
    {
        $constraints = [
            new SignedWith($this->config->signer(), $this->config->verificationKey()),
        ];
        $jwtToken = JwtToken::where('unique_id', $token->claims()->get('user_uuid'))->first();
        if (!$jwtToken) {
            return false;
        }

        return $this->config->validator()->validate($token, ...$constraints);
    }

    public function deleteToken(Plain $token): void
    {
        JwtToken::where('unique_id', $token->claims()->get('user_uuid'))->delete();
    }

    public function getUserFromToken(string $token): ?User
    {
        $parsedToken = $this->parseToken($token);
        if (!$this->validateToken($parsedToken)) {
            return null;
        }

        $userId = $parsedToken->claims()->get('user_uuid');
        return User::where('uuid',$userId)->first();
    }

    public function generateTokenForPasswordReset(User $user)
    {
        return $this->buildToken($user);
    }

    private function buildToken(User $user): Plain
    {
        $appURL = config('app.url');
        $now = new \DateTimeImmutable();
        $token = $this->config->builder()
            ->issuedBy($appURL)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify('+1 hour'))
            ->withClaim('user_uuid', $user->uuid)
            ->getToken($this->config->signer(), $this->config->signingKey());

        return $token;
    }
}
