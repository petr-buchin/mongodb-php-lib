<?php

namespace Tequila\MongoDB\OptionsResolver\BulkWrite;

use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class BulkWriteResolver extends OptionsResolver
{
    protected function configureOptions()
    {
        $this->setDefined([
            'bypassDocumentValidation',
            'ordered',
        ]);

        $this
            ->setAllowedTypes('bypassDocumentValidation', 'bool')
            ->setAllowedTypes('ordered', 'bool');
    }
}