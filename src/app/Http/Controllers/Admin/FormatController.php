<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\FormatResource;
use App\Interfaces\FormatRepositoryInterface;
use App\Models\Format;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="FormatResponse",
 *     @OA\Property(property="worldId", type="string", example="string"),
 *     @OA\Property(property="type", type="string", example="string"),
 *     @OA\Property(
 *        property="translations",
 *        type="array",
 *        @OA\Items(ref="#/components/schemas/FormatLangModel")
 *     ),
 *    ),
 * ),
 * @OA\Schema(
 *     schema="FormatLangModel",
 *     required={"key", "name", "description", "language"},
 *     @OA\Property(
 *         property="key",
 *         type="string"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string"
 *     ),
 *     @OA\Property(
 *         property="language",
 *         type="string"
 *     )
 * ),
 * @OA\Schema(
 *     schema="FormatErrorUnauthenticated",
 *     @OA\Property(property="message", type="string", example="Unauthenticated.")
 * ),
 * @OA\Schema(
 *     schema="FormatErrorUnauthorized",
 *     @OA\Property(property="message", type="string", example="This action is unauthorized.")
 * ),
 * @OA\Schema(
 *     schema="NoFormatExists",
 *      description="Returns when no format exists.",
 *      @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Format]"),
 * ),
 * @OA\Schema(
 *     schema="GetFormatsResponse",
 *             type="array",
 *                example={{
 *                  "id": 0,
 *                  "worldId": "string",
 *                  "type": "string",
 *                  "translations": {
 *                    "key": "string",
 *                    "name": "string",
 *                    "description": "string",
 *                    "language": "string"
 *                  }
 *                }},
 *                @OA\Items(
 *     @OA\Property(property="id", type="integer", example="0"),
 *     ref="#/components/schemas/FormatResponse"),
 * )
 */
class FormatController extends ResourceController
{
    private FormatRepositoryInterface $formatRepository;

    public function __construct(FormatRepositoryInterface $formatRepository)
    {
        $this->formatRepository = $formatRepository;
        $this->authorizeResource(Format::class);
    }

    /**
     * @OA\Get(
     * path="/api/v1/formats",
     * summary="Retrieve formats",
     * description="Retrieve formats for server.",
     * operationId="getFormats",
     * tags={"formats"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     *     response=200,
     *     description="Returns when user is authenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/GetFormatsResponse")
     *   ),
     *   @OA\Response(
     *      response=401,
     *      description="Returns when not authenticated",
     *      @OA\JsonContent(ref="#/components/schemas/FormatErrorUnauthenticated")
     *   )
     * )
     */
    public function index(Request $request)
    {
        $formats = $this->formatRepository->getAsTranslations();
        return FormatResource::collection($formats);
    }

    /**
     * @OA\Get(
     * path="/api/v1/formats/{formatId}",
     * summary="Retrieve a single format",
     * description="Retrieve single format.",
     * operationId="getSingleFormat",
     * tags={"formats"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *    description="ID of format",
     *    in="path",
     *    name="formatId",
     *    required=true,
     *    example="1",
     *    @OA\Schema(
     *       type="integer",
     *       format="int64"
     *    )
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Returns with successful request.",
     *     @OA\JsonContent(
     *     @OA\Property(property="id", type="integer", example="0"),
     *     @OA\Property(property="worldId", type="string", example="string"),
     *     @OA\Property(property="type", type="string", example="string"),
     *     @OA\Property(
     *        property="translations",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/FormatLangModel")
     *     ),
     *   )
     * )
     *   ),
     *   @OA\Response(
     *      response=401,
     *      description="Returns when not authenticated.",
     *      @OA\JsonContent(ref="#/components/schemas/FormatErrorUnauthenticated")
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="Returns when no format exists.",
     *      @OA\JsonContent(ref="#/components/schemas/NoFormatExists")
     *   )
     * )
     */
    public function show(Format $format)
    {
        return new FormatResource($format);
    }

    /**
     * @OA\Post(
     * path="/api/v1/formats",
     * summary="Create Format",
     * description="Cretaes a format.",
     * operationId="createFormat",
     * tags={"formats"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass in format object",
     *    @OA\JsonContent(
     *       required={"translations"},
     *     @OA\Property(property="worldId", type="string", example="string"),
     *     @OA\Property(property="type", type="string", example="string"),
     *     @OA\Property(
     *        property="translations",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/FormatLangModel")
     *     ),
     *    ),
     * ),
     * @OA\Response(
     *    response=201,
     *    description="Returns when POST is successful.",
     *    @OA\JsonContent(
     *     @OA\Property(property="id", type="integer", example="0"),
     *     ref="#/components/schemas/FormatResponse")
     * ),
     * @OA\Response(
     *     response=422,
     *     description="Validation error.",
     *     @OA\JsonContent(
     *        @OA\Property(property="message", type="string", example="The translations.0.key field is required. (and 1 more error)"),
     *        @OA\Property(
     *           property="errors",
     *           type="object",
     *           @OA\Property(
     *              property="translations.0.key",
     *              type="array",
     *              @OA\Items(
     *                 type="string",
     *                 example="The translations.0.key field is required.",
     *              )
     *           ),
     *           @OA\Property(
     *              property="translations.0.name",
     *              type="array",
     *              @OA\Items(
     *                 type="string",
     *                 example="The translations.0.name field is required.",
     *              )
     *           )
     *        )
     *     )
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated.",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="The provided credentials are incorrect."),
     *    )
     * ),
     * @OA\Response(
     *    response=403,
     *    description="Returns when user is unauthorized to perform action.",
     *    @OA\JsonContent(ref="#/components/schemas/FormatErrorUnauthenticated")
     * )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'worldId' => 'nullable|string|max:30',
            'type' => ['nullable', Rule::in(array_keys(Format::TYPE_TO_COMDEF_TYPE_MAP))],
            'translations' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    foreach (collect($value)->groupBy('language') as $translations) {
                        if (count($translations) > 1) {
                            $fail(':attribute may have only one translation per language.');
                        }
                    }
                }
            ],
            'translations.*.key' => ['required', 'string', 'max:10', Rule::notIn(['VM', 'HY', 'TC'])],
            'translations.*.name' => 'required|string|max:255',
            'translations.*.description' => 'required|string|max:255',
            'translations.*.language' => 'required|string|max:7',
        ]);

        $sharedFormatsValues = collect($validated['translations'])->map(function ($translation) use ($validated) {
            return [
                'format_type_enum' => !is_null($validated['type']) ? Format::TYPE_TO_COMDEF_TYPE_MAP[$validated['type']] : null,
                'worldid_mixed' => $validated['worldId'] ?? null,
                'lang_enum' => $translation['language'],
                'key_string' => $translation['key'],
                'name_string' => $translation['name'],
                'description_string' => $translation['description'],
            ];
        })->toArray();

        $format = $this->formatRepository->create($sharedFormatsValues);

        return new FormatResource($format);
    }

    /**
     * @OA\Put(
     * path="/api/v1/formats/{formatId}",
     * summary="Update single format",
     * description="Updates a single format.",
     * operationId="updateFormat",
     * tags={"formats"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *    description="ID of format",
     *    in="path",
     *    name="formatId",
     *    required=true,
     *    example="1",
     *    @OA\Schema(
     *       type="integer",
     *       format="int64"
     *    )
     * ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass in format object",
     *    @OA\JsonContent(ref="#/components/schemas/FormatResponse"),
     * ),
     * @OA\Response(
     *    response=204,
     *    description="Returns when PUT is successful."
     * ),
     * @OA\Response(
     *     response=422,
     *     description="Validation error.",
     *     @OA\JsonContent(
     *        @OA\Property(property="message", type="string", example="The translations.0.key field is required. (and 1 more error)"),
     *        @OA\Property(
     *           property="errors",
     *           type="object",
     *           @OA\Property(
     *              property="translations.0.key",
     *              type="array",
     *              @OA\Items(
     *                 type="string",
     *                 example="The translations.0.key field is required.",
     *              )
     *           ),
     *           @OA\Property(
     *              property="translations.0.name",
     *              type="array",
     *              @OA\Items(
     *                 type="string",
     *                 example="The translations.0.name field is required.",
     *              )
     *           )
     *        )
     *     )
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated.",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="The provided credentials are incorrect."),
     *    )
     * ),
     * @OA\Response(
     *    response=403,
     *    description="Returns when user is unauthorized to perform action.",
     *    @OA\JsonContent(ref="#/components/schemas/FormatErrorUnauthenticated")
     * )
     * )
     */
    public function update(Request $request, Format $format)
    {
        $validated = $request->validate([
            'worldId' => 'nullable|string|max:30',
            'type' => ['nullable', Rule::in(array_keys(Format::TYPE_TO_COMDEF_TYPE_MAP))],
            'translations' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    foreach (collect($value)->groupBy('language') as $translations) {
                        if (count($translations) > 1) {
                            $fail(':attribute may have only one translation per language.');
                        }
                    }
                }
            ],
            'translations.*.key' => ['required', 'string', 'max:10', Rule::notIn(['VM', 'HY', 'TC'])],
            'translations.*.name' => 'required|string|max:255',
            'translations.*.description' => 'required|string|max:255',
            'translations.*.language' => 'required|string|max:7',
        ]);

        $sharedFormatsValues = collect($validated['translations'])->map(function ($translation) use ($validated) {
            return [
                'format_type_enum' => isset($validated['type']) && !is_null($validated['type']) ? Format::TYPE_TO_COMDEF_TYPE_MAP[$validated['type']] : null,
                'worldid_mixed' => $validated['worldId'] ?? null,
                'lang_enum' => $translation['language'],
                'key_string' => $translation['key'],
                'name_string' => $translation['name'],
                'description_string' => $translation['description'],
            ];
        })->toArray();

        $this->formatRepository->update($format->shared_id_bigint, $sharedFormatsValues);

        return response()->noContent();
    }

    /**
     * @OA\Patch(
     * path="/api/v1/formats/{formatId}",
     * summary="Patches a single format",
     * description="Patches a single format by id.",
     * operationId="patchFormat",
     * tags={"formats"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *    description="ID of format",
     *    in="path",
     *    name="formatId",
     *    required=true,
     *    example="1",
     *    @OA\Schema(
     *       type="integer",
     *       format="int64"
     *    )
     * ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass in format attributes.",
     *    @OA\JsonContent(ref="#/components/schemas/FormatResponse"),
     * ),
     * @OA\Response(
     *     response=204,
     *     description="Returns with successful request."
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when not authenticated.",
     *    @OA\JsonContent(ref="#/components/schemas/FormatErrorUnauthenticated")
     * ),
     * @OA\Response(
     *    response=403,
     *    description="Returns when unauthorized.",
     *    @OA\JsonContent(
     *       @OA\Property(ref="#/components/schemas/FormatErrorUnauthorized"),
     *    )
     * ),
     *  @OA\Response(
     *     response=404,
     *     description="Returns when no service body exists.",
     *     @OA\JsonContent(ref="#/components/schemas/NoFormatExists")
     *  )
     * )
     */
    public function partialUpdate(Request $request, Format $format)
    {
        $validated = $request->validate([
            'worldId' => 'nullable|string|max:30',
            'type' => ['nullable', Rule::in(array_keys(Format::TYPE_TO_COMDEF_TYPE_MAP))],
            'translations' => [
                'array',
                'min:1',
                function ($attribute, $value, $fail) {
                    foreach (collect($value)->groupBy('language') as $translations) {
                        if (count($translations) > 1) {
                            $fail(':attribute may have only one translation per language.');
                        }
                    }
                }
            ],
            'translations.*.key' => ['required', 'string', 'max:10', Rule::notIn(['VM', 'HY', 'TC'])],
            'translations.*.name' => 'required|string|max:255',
            'translations.*.description' => 'required|string|max:255',
            'translations.*.language' => 'required|string|max:7',
        ]);

        $sharedFormatsValues = [];
        if (array_key_exists('translations', $validated)) {
            foreach ($validated['translations'] as $translation) {
                $sharedFormatsValues[] = [
                    'format_type_enum' => array_key_exists('type', $validated) ? (!is_null($validated['type']) ? Format::TYPE_TO_COMDEF_TYPE_MAP[$validated['type']] : null) : $format->format_type_enum,
                    'worldid_mixed' => array_key_exists('worldId', $validated) ? $validated['worldId'] ?? null : $format->worldid_mixed,
                    'lang_enum' => $translation['language'],
                    'key_string' => $translation['key'],
                    'name_string' => $translation['name'],
                    'description_string' => $translation['description'],
                ];
            }
        } else {
            foreach ($format->translations as $translation) {
                $sharedFormatsValues[] = [
                    'format_type_enum' => array_key_exists('type', $validated) ? (!is_null($validated['type']) ? Format::TYPE_TO_COMDEF_TYPE_MAP[$validated['type']] : null) : $format->format_type_enum,
                    'worldid_mixed' => array_key_exists('worldId', $validated) ? $validated['worldId'] ?? null : $format->worldid_mixed,
                    'lang_enum' => $translation->lang_enum,
                    'key_string' => $translation->key_string,
                    'name_string' => $translation->name_string,
                    'description_string' => $translation->description_string,
                ];
            }
        }

        $this->formatRepository->update($format->shared_id_bigint, $sharedFormatsValues);

        return response()->noContent();
    }

    /**
     * @OA\Delete(
     * path="/api/v1/formats/{formatId}",
     * summary="Deletes a single format",
     * description="Deletes a single format by id.",
     * operationId="deleteFormat",
     * tags={"formats"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *    description="ID of format",
     *    in="path",
     *    name="formatId",
     *    required=true,
     *    example="1",
     *    @OA\Schema(
     *       type="integer",
     *       format="int64"
     *    )
     * ),
     * @OA\Response(
     *     response=204,
     *     description="Returns with successful request."
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when not authenticated",
     *    @OA\JsonContent(ref="#/components/schemas/FormatErrorUnauthenticated")
     * ),
     * @OA\Response(
     *    response=403,
     *    description="Returns when unauthorized.",
     *    @OA\JsonContent(ref="#/components/schemas/FormatErrorUnauthorized")
     * ),
     *  @OA\Response(
     *     response=404,
     *     description="Returns when no format for id exists.",
     *     @OA\JsonContent(
     *        @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Format]"),
     *     )
     *  )
     * )
     */
    public function destroy(Request $request, Format $format)
    {
        $request->merge(['id' => $format->shared_id_bigint]);
        $request->validate(['id' => [Rule::notIn([
            $this->formatRepository->getVirtualFormat()->shared_id_bigint,
            $this->formatRepository->getTemporarilyClosedFormat()->shared_id_bigint,
            $this->formatRepository->getHybridFormat()->shared_id_bigint,
        ])]]);
        $this->formatRepository->delete($format->shared_id_bigint);
        return response()->noContent();
    }
}
