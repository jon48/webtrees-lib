<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Certificates
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Certificates\Model;

use Fisharebest\Webtrees\Date;
use Fisharebest\Webtrees\Mime;
use Fisharebest\Webtrees\Tree;
use MyArtJaub\Webtrees\Module\Certificates\Services\UrlObfuscatorService;
use DateTime;

/**
 * Model class for a certificate file.
 */
class Certificate
{
    /**
     * Pattern to extract information from a file name.
     * Specific to the author's workflow.
     * @var string
     */
    private const FILENAME_PATTERN = '/^(?<year>\d{1,4})(\.(?<month>\d{1,2}))?(\.(?<day>\d{1,2}))?( (?<type>[A-Z]{1,2}))?\s(?<descr>.*)/'; //phpcs:ignore Generic.Files.LineLength.TooLong

    /**
     * @var Tree $tree
     */
    private $tree;

    /**
     * @var string $path
     * */
    private $path;

    /**
     * @var string|null $city
     * $city */
    private $city;

    /**
     * @var string|null $filename
     */
    private $filename;

    /**
     * @var string|null $extension
     */
    private $extension;

    /**
     * @var string|null $type
     */
    private $type;

    /**
     * @var string|null $description
     */
    private $description;

    /**
     * @var Date|null $date
     */
    private $date;

    /**
     * Contructor for Certificate
     *
     * @param Tree $tree
     * @param string $path
     */
    public function __construct(Tree $tree, string $path)
    {
        $this->tree = $tree;
        $this->path = $path;
        $this->extractDataFromPath($path);
    }

    /**
     * Populate fields from the filename, based on a predeterminate pattern.
     * Logic specific to the author.
     *
     * @param string $path
     */
    protected function extractDataFromPath(string $path): void
    {
        $path_parts = pathinfo($path);
        $this->city = $path_parts['dirname'];
        $this->filename = $path_parts['filename'];
        $this->extension = $path_parts['extension'] ?? '';

        if (preg_match(self::FILENAME_PATTERN, $this->filename, $match) === 1) {
            $this->type = $match['type'];
            $this->description = $match['descr'];

            $day = $match['day'] ?? '';
            $month_date = DateTime::createFromFormat('m', $match['month'] ?? '');
            $month = $month_date !== false ? strtoupper($month_date->format('M')) : '';
            $year = $match['year'] ?? '';

            $this->date = new Date(sprintf('%s %s %s', $day, $month, $year));
        } else {
            $this->description = $this->filename;
        }
    }

    /**
     * Get the family tree of the certificate
     *
     * @return Tree
     */
    public function tree(): Tree
    {
        return $this->tree;
    }

    /**
     * Get the path of the certificate in the file system.
     *
     * @return string
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * The the path of the certificate, in a Gedcom canonical form.
     *
     * @return string
     */
    public function gedcomPath(): string
    {
        return str_replace('\\', '/', $this->path);
    }

    /**
     * Get the certificate name.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->filename ?? '';
    }

    /**
     * Get the certificate's city (the first level folder).
     *
     * @return string
     */
    public function city(): string
    {
        return $this->city ?? '';
    }

    /**
     * Get the certificate's date. Extracted from the file name.
     *
     * @return Date
     */
    public function date(): Date
    {
        return $this->date ?? new Date('');
    }

    /**
     * Get the certificate's type. Extracted from the file name.
     *
     * @return string
     */
    public function type(): string
    {
        return $this->type ?? '';
    }

    /**
     * Get the certificate's description.  Extracted from the file name.
     * @return string
     */
    public function description(): string
    {
        return $this->description ?? '';
    }

    /**
     * Get the certificate's description to be used for sorting.
     * This is based on surnames (at least 3 letters) found in the file name.
     *
     * @return string
     */
    public function sortDescription(): string
    {
        $sort_prefix = '';
        if (preg_match_all('/\b([A-Z]{3,})\b/', $this->description(), $matches, PREG_SET_ORDER) >= 1) {
            $sort_prefix = implode('_', array_map(function ($match) {
                return $match[1];
            }, $matches)) . '_';
        }
        return $sort_prefix . $this->description();
    }

    /**
     * Get the certificate's MIME type.
     *
     * @return string
     */
    public function mimeType(): string
    {
        return Mime::TYPES[$this->extension] ?? Mime::DEFAULT_TYPE;
    }

    /**
     * Get the base parameters to be used in url referencing the certificate.
     *
     * @param UrlObfuscatorService $url_obfuscator_service
     * @return array
     */
    public function urlParameters(UrlObfuscatorService $url_obfuscator_service = null): array
    {
        $url_obfuscator_service = $url_obfuscator_service ?? app(UrlObfuscatorService::class);
        return [
            'tree' => $this->tree->name(),
            'cid' => $url_obfuscator_service->obfuscate($this->path)
        ];
    }
}
