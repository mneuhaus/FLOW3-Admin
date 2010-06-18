<?php
declare(ENCODING = 'utf-8');
namespace F3\Admin\Security\Voter;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * An access decision voter, that asks the FLOW3 PolicyService for a decision.
 *
 * @version $Id: Policy.php 3881 2010-02-27 23:21:00Z andi $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Policy extends \F3\FLOW3\Security\Authorization\Voter\Policy {

	/**
	 * The policy service
	 * @var \F3\FLOW3\Security\Policy\PolicyService
	 */
	protected $policyService;

	/**
	 * Constructor.
	 *
	 * @param F3\FLOW3\Security\Policy\PolicyService $policyService The policy service
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function __construct(\F3\FLOW3\Security\Policy\PolicyService $policyService) {
		$this->policyService = $policyService;
	}

	/**
	 * This is the default Policy voter, it votes for the access privilege for the given join point
	 *
	 * @param F3\FLOW3\Security\Context $securityContext The current securit context
	 * @param F3\FLOW3\AOP\JoinPointInterface $joinPoint The joinpoint to vote for
	 * @return integer One of: VOTE_GRANT, VOTE_ABSTAIN, VOTE_DENY
	 */
	public function voteForJoinPoint(\F3\FLOW3\Security\Context $securityContext, \F3\FLOW3\AOP\JoinPointInterface $joinPoint) {

        $proxy = $joinPoint->getProxy();
        if($proxy instanceof \F3\Admin\Controller\StandardController){
            $arguments = $joinPoint->getMethodArguments();
            if( isset($arguments["being"]) ){
                $arguments["action"] = $proxy->getAction();
                $accessGrants = 0;
                $accessDenies = 0;
                foreach ($securityContext->getAuthenticationTokens() as $token){
                    if(is_callable(array($token,"getUser"))){
                        $user = $token->getUser();
                        if($user->getAdmin())
                            return self::VOTE_GRANT;
                        
                        foreach ($user->getRoles() as $role) {
                            foreach ($role->getGrant() as $policy) {
                                if($this->comparePolicy($arguments, $policy)) $accessGrants++;
                            }
                            #foreach ($role->getDeny() as $policy) {
                            #    if($this->comparePolicy($arguments, $policy)) $accessDenies++;
                            #}
                        }
                    }
                }
                if ($accessDenies > 0) return self::VOTE_DENY;
                if ($accessGrants > 0) return self::VOTE_GRANT;
            }else{
                return self::VOTE_GRANT;
            }
        }

		return self::VOTE_ABSTAIN;
	}

    /**
	 * This is the default Policy voter, it votes for the access privilege for the given resource
	 *
	 * @param F3\FLOW3\Security\Context $securityContext The current securit context
	 * @param string $resource The resource to vote for
	 * @return integer One of: VOTE_GRANT, VOTE_ABSTAIN, VOTE_DENY
	 */
	public function voteForResource(\F3\FLOW3\Security\Context $securityContext, $resource) {
		$accessGrants = 0;
		$accessDenies = 0;
		foreach ($securityContext->getRoles() as $role) {
			$privilege = $this->policyService->getPrivilegeForResource($role, $resource);
			if ($privilege === NULL) continue;

			if ($privilege === \F3\FLOW3\Security\Policy\PolicyService::PRIVILEGE_GRANT) $accessGrants++;
			else $accessDenies++;
		}

		if ($accessDenies > 0) return self::VOTE_DENY;
		if ($accessGrants > 0) return self::VOTE_GRANT;

		return self::VOTE_ABSTAIN;
	}

    public function comparePolicy($arguments,$policy){
        $being = $policy->getBeing();
        $action = $policy->getAction();

        if( $being == $arguments["being"]  && $action == $arguments["action"] )
            return true;
        
        return false;
    }
}

?>