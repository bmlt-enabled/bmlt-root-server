<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    private const FORMATS_TABLE_NAME = 'comdef_formats';

    private const OPEN_FORMAT_TRANSLATIONS = [
        ['key_string' => 'O', 'worldid_mixed' => 'OPEN', 'lang_enum' => 'de', 'name_string' => 'Open', 'description_string' => 'This meeting is open to addicts and non-addicts alike. All are welcome.', 'format_type_enum' => 'O'],
        ['key_string' => 'O', 'worldid_mixed' => 'OPEN', 'lang_enum' => 'dk', 'name_string' => 'Open', 'description_string' => 'This meeting is open to addicts and non-addicts alike. All are welcome.', 'format_type_enum' => 'O'],
        ['key_string' => 'O', 'worldid_mixed' => 'OPEN', 'lang_enum' => 'en', 'name_string' => 'Open', 'description_string' => 'This meeting is open to addicts and non-addicts alike. All are welcome.', 'format_type_enum' => 'O'],
        ['key_string' => 'O', 'worldid_mixed' => 'OPEN', 'lang_enum' => 'es', 'name_string' => 'Abierta', 'description_string' => 'Esta reunión está abierta a los adictos y a los no adictos por igual. Todos son bienvenidos.', 'format_type_enum' => 'O'],
        ['key_string' => 'O', 'worldid_mixed' => 'OPEN', 'lang_enum' => 'fr', 'name_string' => 'Ouvert', 'description_string' => 'Cette réunion est ouverte aux toxicomanes et non-toxicomanes de même. Tous sont les bienvenus.', 'format_type_enum' => 'O'],
        ['key_string' => 'O', 'worldid_mixed' => 'OPEN', 'lang_enum' => 'pl', 'name_string' => 'Otwarty', 'description_string' => 'Mityng otwarty dla uzależnionych i nieuzależnionych. Wszyscy są mile widziani.', 'format_type_enum' => 'O'],
        ['key_string' => 'A', 'worldid_mixed' => 'OPEN', 'lang_enum' => 'pt', 'name_string' => 'Aberta', 'description_string' => 'Esta reunião é aberta para adictos e não-adictos. Todos são bem-vindos.', 'format_type_enum' => 'O'],
        ['key_string' => 'O', 'worldid_mixed' => 'OPEN', 'lang_enum' => 'ru', 'name_string' => 'Открытая', 'description_string' => 'Эта встреча открыта как для наркоманов, так и для не наркоманов. Все приветствуются.', 'format_type_enum' => 'O'],
        ['key_string' => 'Ö', 'worldid_mixed' => 'OPEN', 'lang_enum' => 'sv', 'name_string' => 'Öppet möte', 'description_string' => 'Ett öppet möte är ett NA-möte där vem som helst som är intresserad av hur vi har funnit tillfrisknande från beroendesjukdomen kan närvara.', 'format_type_enum' => 'O'],
        ['key_string' => 'O', 'worldid_mixed' => 'OPEN', 'lang_enum' => 'fa', 'name_string' => 'باز', 'description_string' => 'این جلسه برای کلیه اعضا معتاد و همچنین غیر معتادان باز میباشد', 'format_type_enum' => 'O'],
    ];

    private const CLOSED_FORMAT_TRANSLATIONS = [
        ['key_string' => 'C', 'worldid_mixed' => 'CLOSED', 'lang_enum' => 'de', 'name_string' => 'Closed', 'description_string' => 'This meeting is closed to non-addicts. You should attend only if you believe that you may have a problem with substance abuse.', 'format_type_enum' => 'O'],
        ['key_string' => 'C', 'worldid_mixed' => 'CLOSED', 'lang_enum' => 'dk', 'name_string' => 'Closed', 'description_string' => 'This meeting is closed to non-addicts. You should attend only if you believe that you may have a problem with substance abuse.', 'format_type_enum' => 'O'],
        ['key_string' => 'C', 'worldid_mixed' => 'CLOSED', 'lang_enum' => 'en', 'name_string' => 'Closed', 'description_string' => 'This meeting is closed to non-addicts. You should attend only if you believe that you may have a problem with substance abuse.', 'format_type_enum' => 'O'],
        ['key_string' => 'C', 'worldid_mixed' => 'CLOSED', 'lang_enum' => 'es', 'name_string' => 'Cerrado', 'description_string' => 'Esta reunión está cerrada a los no adictos. Usted debe asistir solamente si cree que puede tener un problema con abuso de drogas.', 'format_type_enum' => 'O'],
        ['key_string' => 'C', 'worldid_mixed' => 'CLOSED', 'lang_enum' => 'fr', 'name_string' => 'Fermée', 'description_string' => 'Cette réunion est fermée aux non-toxicomanes. Vous pouvez y assister que si vous pensez que vous pouvez avoir un problème avec l\'abus de drogues.', 'format_type_enum' => 'O'],
        ['key_string' => 'C', 'worldid_mixed' => 'CLOSED', 'lang_enum' => 'pl', 'name_string' => 'Mityng zamknięty', 'description_string' => 'Mityng zamknięty. Wyłącznie dla osób uzależnionych i tych, które chcą przestać brać.', 'format_type_enum' => 'O'],
        ['key_string' => 'F', 'worldid_mixed' => 'CLOSED', 'lang_enum' => 'pt', 'name_string' => 'Fechada', 'description_string' => 'Esta reunião fechada para não adictos. Você deve ir apenas se acredita ter problemas com abuso de substâncias.', 'format_type_enum' => 'O'],
        ['key_string' => 'C', 'worldid_mixed' => 'CLOSED', 'lang_enum' => 'ru', 'name_string' => 'Закрытая', 'description_string' => 'Эта встреча закрыта для не наркоманов. Вам следует присутствовать только в том случае, если вы считаете, что у вас могут быть проблемы со злоупотреблением психоактивными веществами.', 'format_type_enum' => 'O'],
        ['key_string' => 'S', 'worldid_mixed' => 'CLOSED', 'lang_enum' => 'sv', 'name_string' => 'Slutet möte', 'description_string' => 'Ett slutet NA möte är för de individer som identifierar sig som beroende eller för de som är osäkra och tror att de kanske har drogproblem.', 'format_type_enum' => 'O'],
        ['key_string' => 'C', 'worldid_mixed' => 'CLOSED', 'lang_enum' => 'fa', 'name_string' => 'بسته', 'description_string' => 'این جلسه برای افراد غیر معتاد بسته میباشد. شما تنها اگر فکر میکنید با مواد خدر مشکل دارید میتوانید شرکت کنید', 'format_type_enum' => 'O'],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // add the column
        Schema::table('comdef_meetings_main', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_open')->nullable()->after('venue_type');
        });

        if (legacy_config('aggregator_mode_enabled')) {
            return;
        }


        $openFormatId = $this->getOpenFormatId();
        $closedFormatId = $this->getClosedFormatId();

        // TODO set is_open for all meetings
    }

    public function getOpenFormatId(): int
    {
        $openFormatId = $this->getFormatId('OPEN', 'O');
        $this->addMissingTranslations($openFormatId, self::OPEN_FORMAT_TRANSLATIONS);
        return $openFormatId;
    }

    public function getClosedFormatId(): int
    {
        $closedFormatId = $this->getFormatId('CLOSED', 'C');
        $this->addMissingTranslations($closedFormatId, self::CLOSED_FORMAT_TRANSLATIONS);
        return $closedFormatId;
    }

    public function getFormatId(string $worldId, string $keyString): int
    {
        $formatId = null;

        $possibleIds = $this->getSharedIdsForWorldId($worldId);
        if ($possibleIds->isNotEmpty()) {
            $formatId = $possibleIds->count() == 1 ? $possibleIds->first() : $this->getPreferredFormatId($possibleIds, $keyString);
        }

        if (!is_null($formatId)) {
            return $formatId;
        }

        // TODO test this
        $possibleIds = $this->getSharedIdsForLanguageAndKey('en', $keyString);
        if ($possibleIds->isNotEmpty()) {
            $formatId = $possibleIds->count() == 1 ? $possibleIds->first() : $this->getPreferredFormatId($possibleIds, $keyString);
        }

        if (!is_null($formatId)) {
            return $formatId;
        }

        // TODO test this
        return $this->getNextSharedId();
    }

    public function getPreferredFormatId(Collection $possibleIds, string $preferredKeyString): int
    {
        // TODO test this method

        $formats = DB::table(self::FORMATS_TABLE_NAME)
            ->whereIn('shared_id_bigint', $possibleIds)
            ->orderBy('shared_id_bigint')
            ->get();

        // see if there is an english version with the preferred key string
        $english = $formats
            ->where('lang_enum', 'en')
            ->where('key_string', $preferredKeyString);

        if ($english->isNotEmpty()) {
            $formats = $english;
        }

        // now take the one with the most
        $counts = $formats->mapWithKeys(fn ($fmt, $_) => [$this->getNumMeetingsWithFormatId($fmt->shared_id_bigint) => $fmt->shared_id_bigint]);
        return $counts[$counts->keys()->max()];
    }

    public function getSharedIdsForWorldId(string $worldId): Collection
    {
        return DB::table(self::FORMATS_TABLE_NAME)
            ->where('worldid_mixed', $worldId)
            ->pluck('shared_id_bigint')
            ->unique();
    }

    public function getSharedIdsForLanguageAndKey(string $language, string $key): Collection
    {
        return DB::table(self::FORMATS_TABLE_NAME)
            ->where('lang_enum', $language)
            ->where('key_string', $key)
            ->pluck('shared_id_bigint')
            ->unique();
    }

    public function getNextSharedId(): int
    {
        return DB::table(self::FORMATS_TABLE_NAME)->max('shared_id_bigint') + 1;
    }

    public function getNumMeetingsWithFormatId(int $formatId): int
    {
        return DB::table('comdef_meetings_main')
            ->where(function (Builder $query) use ($formatId) {
                $query
                    ->orWhere('formats', "$formatId")
                    ->orWhere('formats', 'LIKE', "$formatId,%")
                    ->orWhere('formats', 'LIKE', "%,$formatId,%")
                    ->orWhere('formats', 'LIKE', "%,$formatId");
            })
            ->count();
    }

    public function addMissingTranslations(int $sharedId, array $translations)
    {
        $translations = collect($translations);

        // Make sure format_type_enum is correct
        $worldId = $translations->first()['worldid_mixed'];
        DB::table(self::FORMATS_TABLE_NAME)
            ->where('shared_id_bigint', $sharedId)
            ->update(['format_type_enum' => 'O', 'worldid_mixed' => $worldId]);

        // Make sure the english translation is using the expected key_string
        $keyString = $translations->firstWhere('lang_enum', 'en')['key_string'];
        DB::table(self::FORMATS_TABLE_NAME)
            ->where('shared_id_bigint', $sharedId)
            ->where('lang_enum', 'en')
            ->update(['key_string' => $keyString]);

        $existingLanguages = DB::table(self::FORMATS_TABLE_NAME)
            ->where('shared_id_bigint', $sharedId)
            ->pluck('lang_enum');

        $newTranslations = $translations
            ->filter(fn ($t) => !$existingLanguages->contains($t['lang_enum']))
            ->map(fn ($t) => array_merge(['shared_id_bigint' => $sharedId], $t))
            ->toArray();

        if (!empty($newTranslations)) {
            DB::table(self::FORMATS_TABLE_NAME)->insert($newTranslations);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('comdef_meetings_main', function (Blueprint $table) {
            $table->dropColumn('is_open');
        });
    }
};
