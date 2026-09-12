<?php
namespace Tooltipy\Keyword;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Immutable value-object representing a single keyword.
 */
final class KeywordData {

    public readonly int    $id;
    public readonly string $term;
    public readonly string $synonyms;
    public readonly bool   $case_sensitive;
    public readonly bool   $is_prefix;
    public readonly string $families_class;
    public readonly string $youtube_id;
    public readonly string $icon_url;
    public readonly string $thumbnail_html;
    public readonly string $content;

    public function __construct( array $data ) {
        $this->id             = (int)   ( $data['kw_id']         ?? 0 );
        $this->term           = (string)( $data['term']           ?? '' );
        $this->synonyms       = (string)( $data['syns']           ?? '' );
        $this->case_sensitive = (bool)  ( $data['case']           ?? false );
        $this->is_prefix      = (bool)  ( $data['pref']           ?? false );
        $this->families_class = (string)( $data['families_class'] ?? '' );
        $this->youtube_id     = (string)( $data['youtube']        ?? '' );
        $this->icon_url       = (string)( $data['icon']           ?? '' );
        $this->thumbnail_html = (string)( $data['img']            ?? '' );
        $this->content        = (string)( $data['dfn']            ?? '' );
    }

    /** Returns the term + synonyms piped-joined (e.g. "PHP|php") */
    public function get_full_term(): string {
        if ( $this->synonyms !== '' ) {
            return $this->term . '|' . $this->synonyms;
        }
        return $this->term;
    }

    public function has_video(): bool {
        return $this->youtube_id !== '';
    }

    public function has_icon(): bool {
        return $this->icon_url !== '';
    }
}
