<?php

namespace Tests\Functional;

use App\Enums\ErrorCodeEnum;
use App\Product;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Tests\TestCase;

/**
 * Class CsvImportTest
 */
class CsvImportTest extends TestCase
{
    private const ASSET_EMPTY = 'empty.file';
    private const ASSET_NON_CSV = 'non-csv.file';
    private const ASSET_ONLY_HEADER = 'only-header.csv';
    private const ASSET_VALID = 'valid.csv';

    private const URI = '/csv/import';

    private const COLUMNS = [
        'A',
        'B',
        'C',
        'D',
    ];

    /**
     *
     */
    public function testSimpleRequestNoData(): void
    {
        $response = $this->json(Request::METHOD_POST, static::URI);
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->assertErrorStructure($response);
    }

    /**
     *
     */
    public function testOnlyCsv(): void
    {
        $response = $this->json(Request::METHOD_POST, static::URI, [
            'csv' => $this->uploadFileOfType(static::ASSET_EMPTY),
        ]);
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->assertErrorStructure($response);
        $this->assertEquals(ErrorCodeEnum::MAP_MODEL_NOT_PASSED, $response->json('error')['code']);
    }

    /**
     *
     */
    public function testNotValidMapModel(): void
    {
        $response = $this->json(Request::METHOD_POST, static::URI, [
            'csv' => $this->uploadFileOfType(static::ASSET_EMPTY),
            'map_to' => 'NonExist',
        ]);
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->assertErrorStructure($response);
        $this->assertEquals(ErrorCodeEnum::MAP_MODEL_NOT_FOUND, $response->json('error')['code']);
    }

    /**
     *
     */
    public function testEmptyFile(): void
    {
        $response = $this->json(Request::METHOD_POST, static::URI, [
            'csv' => $this->uploadFileOfType(static::ASSET_EMPTY),
            'map_to' => Product::class,
        ]);
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->assertErrorStructure($response);
        $this->assertEquals(ErrorCodeEnum::CSV_INVALID, $response->json('error')['code']);
    }

    /**
     *
     */
    public function testNonCsv(): void
    {
        $response = $this->json(Request::METHOD_POST, static::URI, [
            'csv' => $this->uploadFileOfType(static::ASSET_NON_CSV),
            'map_to' => Product::class,
        ]);
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->assertErrorStructure($response);
        $this->assertEquals(ErrorCodeEnum::CSV_INVALID, $response->json('error')['code']);
    }

    /**
     *
     */
    public function testOnlyHeader(): void
    {
        $response = $this->json(Request::METHOD_POST, static::URI, [
            'csv' => $this->uploadFileOfType(static::ASSET_ONLY_HEADER),
            'map_to' => Product::class,
        ]);
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->assertErrorStructure($response);
        $this->assertEquals(ErrorCodeEnum::CSV_INVALID, $response->json('error')['code']);
    }

    /**
     *
     */
    public function testValid(): void
    {
        $response = $this->json(Request::METHOD_POST, static::URI, [
            'csv' => $this->uploadFileOfType(static::ASSET_VALID),
            'map_to' => Product::class,
        ]);
        $response->assertStatus(Response::HTTP_OK);
        $this->assertSuccessStructure($response);

        $data = $response->json('data');
        $this->assertCount(3, $data);

        foreach ($data as $item) {
            foreach (static::COLUMNS as $column) {
                $this->assertArrayHasKey($column, $item);
                $this->assertEquals('val_' . $column, $item[$column]);
            }
        }
    }

    /**
     *
     */
    public function testMappingsInvalid(): void
    {
        $response = $this->json(Request::METHOD_POST, static::URI, [
            'csv' => $this->uploadFileOfType(static::ASSET_VALID),
            'map_to' => Product::class,
            'mappings' => [
                'XXX' => 'xxx',
            ],
        ]);
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->assertErrorStructure($response);
        $this->assertEquals(ErrorCodeEnum::MAPPING_INVALID, $response->json('error')['code']);
    }

    /**
     *
     */
    public function testMappingsValid(): void
    {
        $mappings = [
            static::COLUMNS[0] => strtolower(str_repeat(static::COLUMNS[0], 3)),
            static::COLUMNS[2] => strtolower(str_repeat(static::COLUMNS[2], 3)),
        ];
        $response = $this->json(Request::METHOD_POST, static::URI, [
            'csv' => $this->uploadFileOfType(static::ASSET_VALID),
            'map_to' => Product::class,
            'mappings' => $mappings,
        ]);
        $response->assertStatus(Response::HTTP_OK);
        $this->assertSuccessStructure($response);

        $data = $response->json('data');
        $this->assertCount(3, $data);

        foreach ($data as $item) {
            foreach (static::COLUMNS as $column) {
                if (array_key_exists($column, $mappings)) {
                    $this->assertArrayHasKey($mappings[$column], $item);
                    $this->assertEquals('val_' . $column, $item[$column]);
                } else {
                    $this->assertArrayHasKey($column, $item);
                    $this->assertEquals('val_' . $column, $item[$column]);
                }
            }
        }
    }

    /**
     * @param TestResponse $response
     */
    private function assertErrorStructure(TestResponse $response): void
    {
        $response->assertJsonStructure([
            'error' => [
                'status',
                'code',
                'message',
            ],
        ]);
    }

    /**
     * @param TestResponse $response
     */
    private function assertSuccessStructure(TestResponse $response): void
    {
        $response->assertJsonStructure([
            'data',
        ]);
    }

    /**
     * @param string $type
     * @return UploadedFile
     */
    private function uploadFileOfType(string $type): UploadedFile
    {
        return UploadedFile::createFromBase(
            new SymfonyUploadedFile(sprintf('%s/assets/%s', __DIR__, $type), $type)
        );
    }
}
