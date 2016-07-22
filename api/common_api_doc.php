<?php
/**
 * @apiDefine public Public
 *      A public API can be utilized by anyone, without the use of an `X-Auth-Token`.
 *
 */
/**
 * @apiDefine protected Protected (Any)
 *      A protected API can only be utilized by any user with a valid `X-Auth-Token`.
 */
/**
 * @apiDefine invalidXAuthToken 401 Unauthorized
 *      The `X-Auth-Token` provided is invalid.
 */
/**
 * @apiDefine noTokenProvided 400 Bad Request
 *      No `X-Auth-Token` was provided.
 */
/**
 * @apiDefine canManUsers Protected (Can Manage Users)
 *      A protected API can only be utilized by users with a valid `X-Auth-Token` and have the 'can_man_users' permission (or is an admin)
 */