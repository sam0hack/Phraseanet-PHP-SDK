<?php

/*
 * This file is part of Phraseanet SDK.
 *
 * (c) Alchemy <info@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhraseanetSDK\Repository;

use PhraseanetSDK\EntityHydrator;
use PhraseanetSDK\Exception\RuntimeException;

class User extends AbstractRepository
{
    /**
     * @return \PhraseanetSDK\Entity\User
     * @throws \PhraseanetSDK\Exception\NotFoundException
     * @throws \PhraseanetSDK\Exception\UnauthorizedException
     * @deprecated Use User::me() instead
     */
    public function findMe()
    {
        return $this->me();
    }

    /**
     * @return \PhraseanetSDK\Entity\User
     * @throws \PhraseanetSDK\Exception\NotFoundException
     * @throws \PhraseanetSDK\Exception\UnauthorizedException
     */
    public function me()
    {
        $response = $this->query('GET', 'me/');

        if (!$response->hasProperty('user')) {
            throw new RuntimeException('Missing "user" property in response content');
        }

        /** @var \PhraseanetSDK\Entity\User $user */
        $user = EntityHydrator::hydrate('user', $response->getProperty('user'), $this->em);

        if ($response->hasProperty('collections')) {
            $user->setCollectionRights($response->getProperty('collections'));
        }

        return $user;
    }

    /**
     * @param $emailAddress
     * @return string
     * @throws \PhraseanetSDK\Exception\NotFoundException
     * @throws \PhraseanetSDK\Exception\UnauthorizedException
     */
    public function requestPasswordReset($emailAddress)
    {
        $response = $this->query('POST', 'accounts/reset-password/' . $emailAddress . '/');

        if (!$response->hasProperty('reset_token')) {
            throw new RuntimeException('Missing "token" property in response content');
        }

        return (string)$response->getProperty('reset_token');
    }

    /**
     * @param $token
     * @param $password
     * @return bool
     * @throws \PhraseanetSDK\Exception\NotFoundException
     * @throws \PhraseanetSDK\Exception\UnauthorizedException
     */
    public function resetPassword($token, $password)
    {
        $response = $this->query('POST', 'accounts/update-password/' . $token . '/', array(), array(
            'password' => $password
        ));

        if (!$response->hasProperty('success')) {
            throw new RuntimeException('Missing "success" property in response content');
        }

        return (bool)$response->getProperty('success');
    }

    /**
     * @param \PhraseanetSDK\Entity\User $user
     * @param $password
     * @param int[] $collections
     * @return string
     * @throws \PhraseanetSDK\Exception\NotFoundException
     * @throws \PhraseanetSDK\Exception\UnauthorizedException
     */
    public function createUser(\PhraseanetSDK\Entity\User $user, $password, array $collections = null)
    {
        $data = array(
            'email' => $user->getEmail(),
            'password' => $password,
            'gender' => $user->getGender(),
            'firstname' => $user->getFirstName(),
            'lastname' => $user->getLastName(),
            'city' => $user->getCity(),
            'tel' => $user->getPhone(),
            'company' => $user->getCompany(),
            'job' => $user->getJob(),
            'notifications' => false
        );

        if ($collections !== null) {
            $data['collections'] = $collections;
        }

        $response = $this->query('POST', 'accounts/access-demand/', array(), $data,
            array('Content-Type' => 'application/json'));

        if (!$response->hasProperty('user')) {
            throw new \RuntimeException('Missing "user" property in response content');
        }

        if (!$response->hasProperty('token')) {
            throw new \RuntimeException('Missing "token" property in response content');
        }

        return (string)$response->getProperty('token');
    }

    public function updateUser(\PhraseanetSDK\Entity\User $user)
    {
        $data = array(
            'email' => $user->getEmail(),
            'gender' => $user->getGender(),
            'firstname' => $user->getFirstName(),
            'lastname' => $user->getLastName(),
            'city' => $user->getCity(),
            'tel' => $user->getPhone(),
            'company' => $user->getCompany(),
            'job' => $user->getJob(),
            'notifications' => false
        );

        $response = $this->query('POST', 'accounts/update-account/' . $user->getEmail(), array(), $data,
            array('Content-Type' => 'application/json'));

        return (bool)$response->getProperty('success');
    }

    /**
     * @param $token
     * @return bool
     * @throws \PhraseanetSDK\Exception\NotFoundException
     * @throws \PhraseanetSDK\Exception\UnauthorizedException
     */
    public function unlockAccount($token)
    {
        $response = $this->query('POST', 'accounts/unlock/' . $token . '/', array(), array());

        if (!$response->hasProperty('success')) {
            throw new \RuntimeException('Missing "success" property in response content');
        }

        return (bool)$response->getProperty('success');
    }
}
