<?php

namespace eLife\Recommendations\Rule;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Recommendations\Relationships\ManyToManyRelationship;
use eLife\Recommendations\Rule;
use eLife\Recommendations\Rule\Common\GetSdk;
use eLife\Recommendations\Rule\Common\PersistRule;
use eLife\Recommendations\Rule\Common\RepoRelations;
use eLife\Recommendations\RuleModel;
use eLife\Recommendations\RuleModelRepository;

class PodcastEpisodeContents implements Rule
{
    use GetSdk;
    use PersistRule;
    use RepoRelations;

    private $sdk;
    private $repo;

    public function __construct(ApiSdk $sdk, RuleModelRepository $repo)
    {
        $this->sdk = $sdk;
        $this->repo = $repo;
    }

    public function resolveRelations(RuleModel $input): array
    {
        /** @var PodcastEpisode $model Added to stop IDE complaining @todo create hasSubjects interface. */
        $model = $this->getFromSdk($input->getType(), $input->getId());
        $relations = [];
        foreach ($model->getChapters() as $chapter) {
            $content = $chapter->getContent();
            $relations[] = $content->filter(function ($content) {
                // Only article for now in this rule.
                return $content instanceof ArticleVersion;
            })->map(function (ArticleVersion $article) use ($input) {
                // Link this podcast TO the related item.
                return new ManyToManyRelationship(new RuleModel($article->getId(), 'research-article', $article->getPublishedDate()), $input);
            })->toArray();
        }

        return array_reduce($relations, 'array_merge', []);
    }

    public function supports(): array
    {
        return [
            'podcast-episode',
        ];
    }
}
