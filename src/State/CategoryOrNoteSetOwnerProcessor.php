<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Category;
use App\Entity\Note;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CategoryOrNoteSetOwnerProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        protected ProcessorInterface $innerProcessor,
        protected Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (
            ($data instanceof Category || $data instanceof Note) &&
            $data->getOwner() === null && $this->security->getUser()
        ) {
            $data->setOwner($this->security->getUser());

            if ($data instanceof Note) {
                foreach ($data->getSubtasks() as $subtask) {
                    $subtask->setOwner($this->security->getUser());
                }
            }
        }

        return $this->innerProcessor->process($data, $operation, $uriVariables, $context);
    }
}
