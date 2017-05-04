<?php
/*
 * 2017 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Configuration Object project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\ConfigurationObject\Validation;

use Romm\ConfigurationObject\Core\Core;
use Romm\ConfigurationObject\Reflection\ReflectionService;
use Romm\ConfigurationObject\Validation\Validator\Internal\ConfigurationObjectValidator;
use Romm\ConfigurationObject\Validation\Validator\Internal\MixedTypeCollectionValidator;
use TYPO3\CMS\Extbase\Reflection\ReflectionService as ExtbaseReflectionService;
use TYPO3\CMS\Extbase\Validation\Validator\CollectionValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator;
use TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator;

/**
 * Customized validator resolver, which it mostly used to support the mixed
 * types.
 *
 * When an instance of validator is created, we check if the type of this
 * validator is `CollectionValidator`: in this case we use a custom one instead:
 * `MixedTypeCollectionValidator` which will support the mixed types feature.
 */
class ValidatorResolver extends \TYPO3\CMS\Extbase\Validation\ValidatorResolver
{

    /**
     * @var array
     */
    protected $baseValidatorConjunctionsWithChecks = [];

    /**
     * @inheritdoc
     */
    public function createValidator($validatorType, array $validatorOptions = [])
    {
        return (CollectionValidator::class === $validatorType)
            ? parent::createValidator(MixedTypeCollectionValidator::class)
            : parent::createValidator($validatorType, $validatorOptions);
    }

    /**
     * @inheritdoc
     */
    protected function buildBaseValidatorConjunction($indexKey, $targetClassName, array $validationGroups = [])
    {
        parent::buildBaseValidatorConjunction($indexKey, $targetClassName, $validationGroups);

        /*
         * The code below is DIRTY: in order to use `SilentExceptionInterface`
         * feature we need an extended version of the `GenericObjectValidator`,
         * but this is hardcoded in:
         * \TYPO3\CMS\Extbase\Validation\ValidatorResolver::buildBaseValidatorConjunction()
         *
         * Here we replace every `GenericObjectValidator` by our own instance.
         *
         * Please, do not try this at home.
         */
        /** @var ConjunctionValidator $conjunctionValidator */
        $conjunctionValidator = $this->baseValidatorConjunctions[$indexKey];

        foreach ($conjunctionValidator->getValidators() as $validator) {
            if ($validator instanceof GenericObjectValidator) {
                /** @var ConfigurationObjectValidator $newValidator */
                $newValidator = $this->objectManager->get(ConfigurationObjectValidator::class, []);

                foreach ($validator->getPropertyValidators() as $propertyName => $propertyValidators) {
                    foreach ($propertyValidators as $propertyValidator) {
                        $newValidator->addPropertyValidator($propertyName, $propertyValidator);
                    }
                }

                $conjunctionValidator->removeValidator($validator);
                unset($validator);
                $conjunctionValidator->addValidator($newValidator);
            }
        }
    }

    /**
     * @param ExtbaseReflectionService $reflectionService
     */
    public function injectReflectionService(ExtbaseReflectionService $reflectionService)
    {
        $this->reflectionService = Core::get()->getObjectManager()->get(ReflectionService::class);
    }
}
