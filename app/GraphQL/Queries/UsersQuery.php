<?php declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Http\Resources\Api\V1\ResponseResource;
use App\Models\Api\V1\User;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

final class UsersQuery
{
    /**
     * @param  null  $root Always null, since this field has no parent.
     * @param  array{}  $args The field arguments passed by the client.
     * @param  \Nuwave\Lighthouse\Support\Contracts\GraphQLContext  $context Shared between all fields.
     * @param  \GraphQL\Type\Definition\ResolveInfo  $resolveInfo Metadata for advanced query resolution.
     */
    public function __invoke(mixed $_, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): LengthAwarePaginator
    {
        // throw new AuthorizationException('Acesso negado. '. json_encode($args));
        $perPage = $args['first'] ?? $args['limit'] ?? 10;
        $query = User::query();
        if (!empty($args['name'])) {
            $query->where('name', 'like', '%' . $args['name'] . '%');
        }
        if (!empty($args['email'])) {
            $query->where('email', $args['email']);
        }
        return $query->paginate($perPage, ['*'], 'page', $args['page'] ?? 1);
    }
}
