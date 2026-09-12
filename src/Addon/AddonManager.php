<?php
namespace Tooltipy\Addon;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Registry for Tooltipy addons.
 *
 * This class is the foundation of the future monetisation model:
 *   - Tooltipy core is free (like WooCommerce core)
 *   - Paid addons register themselves via the 'tooltipy_register_addons' action
 *   - Each addon implements AddonInterface
 *
 * Example addon registration (from an external plugin):
 *
 *   add_action('tooltipy_register_addons', function(\Tooltipy\Addon\AddonManager $manager) {
 *       $manager->register(new MyPaidAddon());
 *   });
 */
final class AddonManager {

    /** @var AddonInterface[] */
    private array $addons = [];

    /**
     * Fire the registration hook so external addons can register themselves.
     */
    public function init(): void {
        do_action( 'tooltipy_register_addons', $this );
    }

    /**
     * Register an addon and immediately call its init() method.
     */
    public function register( AddonInterface $addon ): void {
        $id = $addon->get_id();

        if ( isset( $this->addons[ $id ] ) ) {
            // Prevent duplicate registration
            return;
        }

        $this->addons[ $id ] = $addon;
        $addon->init();
    }

    /**
     * Check whether an addon with the given ID is registered.
     */
    public function has( string $id ): bool {
        return isset( $this->addons[ $id ] );
    }

    /**
     * Retrieve a registered addon by ID (or null).
     */
    public function get( string $id ): ?AddonInterface {
        return $this->addons[ $id ] ?? null;
    }

    /**
     * Return all registered addons.
     *
     * @return AddonInterface[]
     */
    public function all(): array {
        return $this->addons;
    }

    /**
     * Trigger activation hooks on all registered addons.
     * Call this from your addon plugin's register_activation_hook callback.
     */
    public function activate_all(): void {
        foreach ( $this->addons as $addon ) {
            $addon->activate();
        }
    }

    /**
     * Trigger deactivation hooks on all registered addons.
     */
    public function deactivate_all(): void {
        foreach ( $this->addons as $addon ) {
            $addon->deactivate();
        }
    }
}
