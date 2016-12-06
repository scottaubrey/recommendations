<?php

namespace eLife\Recommendations;

use eLife\Recommendations\Response\Article;
use eLife\Recommendations\Response\Result;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;

final class RecommendationResultDiscriminator implements EventSubscriberInterface
{
    public static $articleTypes = [
        'correction',
        'editorial',
        'feature',
        'insight',
        'research-advance',
        'research-article',
        'research-exchange',
        'retraction',
        'registered-report',
        'replication-study',
        'short-report',
        'tools-resources',
    ];

    public static function getSubscribedEvents()
    {
        return [
            ['event' => Events::PRE_SERIALIZE, 'method' => 'onPreSerialize'],
            ['event' => Events::PRE_DESERIALIZE, 'method' => 'onPreDeserialize'],
        ];
    }

    public function onPreDeserialize(PreDeserializeEvent $event)
    {
        $data = $event->getData();
        if (isset($data['type'])) {
            // Set the internal type as the defined type as per normal.
            $data['internal_type'] = $data['type'];
            // If its one of the research articles..
            if (in_array($data['type'], self::$articleTypes)) {
                // Do the right thing.
                $data['internal_type'] = 'research-article--'.$data['status'];
            }
            $event->setData($data);
        }
    }

    public function onPreSerialize(PreSerializeEvent $event)
    {
        $object = $event->getObject();
        if (is_object($object) && $object instanceof Result) {
            /* @noinspection PhpUndefinedFieldInspection */
            $object->internal_type = $object->getType();
            if ($object instanceof Article) {
                /* @noinspection PhpUndefinedFieldInspection */
                $object->internal_type .= '--'.$object->status;
            }
        }
    }
}
