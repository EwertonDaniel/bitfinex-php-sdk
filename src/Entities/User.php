<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use Carbon\Carbon;
use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Class User
 *
 * Represents a user account on the Bitfinex platform. Provides detailed information about:
 * - Account properties like email, username, and verification status.
 * - Advanced account features such as securities, merchant, and competition functionalities.
 * - Two-Factor Authentication (2FA) settings.
 * - Account group and master account relationships.
 *
 * Key Features:
 * - Handles account verification and functionality flags.
 * - Tracks timestamps for account creation and login activity.
 * - Supports locale, timezone, and residency information.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class User
{
    /** Unique Account ID. */
    public readonly int $id;

    /** Account Email Address. */
    public readonly string $email;

    /** Username associated with the account. */
    public readonly string $username;

    /** Millisecond timestamp of account creation. */
    public readonly int $mtsAccountCreate;

    /** Indicates if the user is KYC verified (true = verified, false = not verified). */
    public readonly bool $verified;

    /** Verification level of the account. */
    public readonly int $verificationLevel;

    /** Account's timezone setting (e.g., UTC). */
    public readonly ?string $timezone;

    /** Account's locale setting (e.g., en_US). */
    public readonly ?string $locale;

    /** Company where the account is registered (e.g., 'bitfinex', 'eosfinex'). */
    public readonly ?string $company;

    /** Indicates if the email is verified (true = verified, false = not verified). */
    public readonly bool $emailVerified;

    /** Millisecond timestamp of the master account creation. */
    public readonly ?int $mtsMasterAccountCreate;

    /** Account group ID. */
    public readonly ?int $groupId;

    /** Master account ID, if the account is a sub-account. */
    public readonly ?int $masterAccountId;

    /** Indicates if the account inherits verification from the master account. */
    public readonly ?bool $inheritMasterAccountVerification;

    /** Indicates if the account is a group master account. */
    public readonly ?bool $isGroupMaster;

    /** Indicates if group withdrawal is enabled. */
    public readonly ?bool $groupWithdrawEnabled;

    /** Indicates if paper trading is enabled. */
    public readonly ?bool $pptEnabled;

    /** Indicates if merchant functionality is enabled. */
    public readonly ?bool $merchantEnabled;

    /** Indicates if competition functionality is enabled. */
    public readonly ?bool $competitionEnabled;

    /** Enabled two-factor authentication modes (e.g., 'u2f', 'otp'). */
    public readonly ?TwoFactorAuthModes $twoFactorAuthModes;

    /** Indicates if the account has a securities sub-account. */
    public readonly ?bool $isSecuritiesMaster;

    /** Indicates if securities functionality is enabled. */
    public readonly ?bool $securitiesEnabled;

    /** Indicates if the account is accredited as an investor. */
    public readonly ?bool $isSecuritiesInvestorAccredited;

    /** Indicates if the account is verified for El Salvador securities. */
    public readonly ?bool $isSecuritiesElSalvador;

    /** Indicates if the account can disable context switching. */
    public readonly ?bool $allowDisableCtxSwitch;

    /** Indicates if the master account cannot switch context to this account. */
    public readonly ?bool $ctxSwitchDisabled;

    /** Date and time of the last login in UTC (ISO 8601 format). */
    public readonly ?Carbon $timeLastLogin;

    /** Highest verification level submitted by the account. */
    public readonly ?int $verificationLevelSubmitted;

    /** Country and region based on verification data (residence and nationality). */
    public readonly ?CountryAndRegion $compCountries;

    /** Country and region based on residence verification data only. */
    public readonly ?CountryAndRegion $compCountriesResid;

    /** Type of verification applied to the account ('individual' or 'corporate'). */
    public readonly ?string $complAccountType;

    /** Indicates if the account is an enterprise merchant. */
    public readonly ?bool $isMerchantEnterprise;

    /**
     * Constructs a User entity using data from the Bitfinex API.
     *
     * @param  array  $data  Array containing user account details.
     */
    public function __construct(array $data)
    {
        $this->id = $data[0];
        $this->email = $data[1];
        $this->username = $data[2];
        $this->mtsAccountCreate = $data[3];
        $this->verified = $data[4] === 1;
        $this->verificationLevel = $data[5];
        $this->timezone = GetThis::ifTrueOrFallback(isset($data[7]), fn () => $data[7]);
        $this->locale = GetThis::ifTrueOrFallback(isset($data[8]), fn () => $data[8]);
        $this->company = GetThis::ifTrueOrFallback(isset($data[9]), fn () => $data[9]);
        $this->emailVerified = $data[10] === 1;
        $this->mtsMasterAccountCreate = GetThis::ifTrueOrFallback(isset($data[14]), fn () => $data[14]);
        $this->groupId = GetThis::ifTrueOrFallback(isset($data[15]), fn () => $data[15]);
        $this->masterAccountId = GetThis::ifTrueOrFallback(isset($data[16]), fn () => $data[16]);
        $this->inheritMasterAccountVerification = GetThis::ifTrueOrFallback(isset($data[17]), fn () => $data[17] === 1, false);
        $this->isGroupMaster = GetThis::ifTrueOrFallback(isset($data[18]), fn () => $data[18] === 1, false);
        $this->groupWithdrawEnabled = GetThis::ifTrueOrFallback(isset($data[19]), fn () => $data[19] === 1, false);
        $this->pptEnabled = GetThis::ifTrueOrFallback(isset($data[21]), fn () => $data[21] === 1, false);
        $this->merchantEnabled = GetThis::ifTrueOrFallback(isset($data[22]), fn () => $data[22] === 1, false);
        $this->competitionEnabled = GetThis::ifTrueOrFallback(isset($data[23]), fn () => $data[23] === 1, false);
        $this->twoFactorAuthModes = GetThis::ifTrueOrFallback(isset($data[26]), fn () => new TwoFactorAuthModes($data[26]));
        $this->isSecuritiesMaster = GetThis::ifTrueOrFallback(isset($data[28]), fn () => $data[28] === 1, false);
        $this->securitiesEnabled = GetThis::ifTrueOrFallback(isset($data[29]), fn () => $data[29] === 1, false);
        $this->isSecuritiesInvestorAccredited = GetThis::ifTrueOrFallback(isset($data[30]), fn () => $data[30] === 1, false);
        $this->isSecuritiesElSalvador = GetThis::ifTrueOrFallback(isset($data[31]), fn () => $data[31] === 1, false);
        $this->allowDisableCtxSwitch = GetThis::ifTrueOrFallback(isset($data[38]), fn () => $data[38] === 1, false);
        $this->ctxSwitchDisabled = GetThis::ifTrueOrFallback(isset($data[39]), fn () => $data[39] === 1, false);
        $this->timeLastLogin = GetThis::ifTrueOrFallback(isset($data[44]), fn () => Carbon::parse($data[44]));
        $this->verificationLevelSubmitted = GetThis::ifTrueOrFallback(isset($data[47]), fn () => $data[47]);
        $this->compCountries = GetThis::ifTrueOrFallback(isset($data[49]) && is_array($data[49]), new CountryAndRegion($data[49]));
        $this->compCountriesResid = GetThis::ifTrueOrFallback(isset($data[50]) && is_array($data[50]), new CountryAndRegion($data[50]));
        $this->complAccountType = GetThis::ifTrueOrFallback(isset($data[51]), fn () => $data[51]);
        $this->isMerchantEnterprise = GetThis::ifTrueOrFallback(isset($data[54]), fn () => $data[54] === 1, false);
    }
}
