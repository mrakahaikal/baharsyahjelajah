<?php

namespace App\Providers;

use Filament\Actions\View\ActionsIconAlias;
use Filament\Forms\View\FormsIconAlias;
use Filament\Notifications\View\NotificationsIconAlias;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\View\SupportIconAlias;
use Filament\Tables\View\TablesIconAlias;
use Filament\View\PanelsIconAlias;
use Illuminate\Support\ServiceProvider;

class FilamentPanelIconProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        FilamentIcon::register([
            // Actions
            ActionsIconAlias::ACTION_GROUP => 'lucide-ellipsis-vertical',
            ActionsIconAlias::CREATE_ACTION_GROUPED => 'lucide-plus',
            ActionsIconAlias::DELETE_ACTION => 'lucide-trash-2',
            ActionsIconAlias::DELETE_ACTION_GROUPED => 'lucide-trash-2',
            ActionsIconAlias::EDIT_ACTION => 'lucide-pencil',
            ActionsIconAlias::EDIT_ACTION_GROUPED => 'lucide-pencil',
            ActionsIconAlias::VIEW_ACTION => 'lucide-eye',
            ActionsIconAlias::VIEW_ACTION_GROUPED => 'lucide-eye',
            ActionsIconAlias::REPLICATE_ACTION => 'lucide-copy',
            ActionsIconAlias::REPLICATE_ACTION_GROUPED => 'lucide-copy',

            // Panels & Shell
            PanelsIconAlias::GLOBAL_SEARCH_FIELD => 'lucide-search',
            PanelsIconAlias::THEME_SWITCHER_LIGHT_BUTTON => 'lucide-sun',
            PanelsIconAlias::THEME_SWITCHER_DARK_BUTTON => 'lucide-moon',
            PanelsIconAlias::THEME_SWITCHER_SYSTEM_BUTTON => 'lucide-monitor',
            PanelsIconAlias::PAGES_DASHBOARD_NAVIGATION_ITEM => 'lucide-layout-dashboard',
            PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON => 'lucide-panel-left-close',
            PanelsIconAlias::SIDEBAR_EXPAND_BUTTON => 'lucide-panel-left-open',
            PanelsIconAlias::SIDEBAR_GROUP_COLLAPSE_BUTTON => 'lucide-chevron-down',
            PanelsIconAlias::TOPBAR_CLOSE_SIDEBAR_BUTTON => 'lucide-x',
            PanelsIconAlias::TOPBAR_OPEN_SIDEBAR_BUTTON => 'lucide-menu',
            PanelsIconAlias::SIDEBAR_OPEN_DATABASE_NOTIFICATIONS_BUTTON => 'lucide-bell',
            PanelsIconAlias::TOPBAR_OPEN_DATABASE_NOTIFICATIONS_BUTTON => 'lucide-bell',
            PanelsIconAlias::USER_MENU_PROFILE_ITEM => 'lucide-user',
            PanelsIconAlias::USER_MENU_LOGOUT_BUTTON => 'lucide-log-out',

            // Tables
            TablesIconAlias::SEARCH_FIELD => 'lucide-search',
            TablesIconAlias::ACTIONS_FILTER => 'lucide-filter',
            TablesIconAlias::ACTIONS_COLUMN_MANAGER => 'lucide-columns-3',
            TablesIconAlias::REORDER_HANDLE => 'lucide-grip-vertical',
            TablesIconAlias::COLUMNS_ICON_COLUMN_TRUE => 'lucide-circle-check',
            TablesIconAlias::COLUMNS_ICON_COLUMN_FALSE => 'lucide-circle-x',

            // Forms
            FormsIconAlias::COMPONENTS_TEXT_INPUT_ACTIONS_SHOW_PASSWORD => 'lucide-eye',
            FormsIconAlias::COMPONENTS_TEXT_INPUT_ACTIONS_HIDE_PASSWORD => 'lucide-eye-off',
            FormsIconAlias::COMPONENTS_TEXT_INPUT_ACTIONS_COPY => 'lucide-copy',
            FormsIconAlias::COMPONENTS_REPEATER_ACTIONS_CLONE => 'lucide-copy',
            FormsIconAlias::COMPONENTS_REPEATER_ACTIONS_DELETE => 'lucide-trash-2',
            FormsIconAlias::COMPONENTS_REPEATER_ACTIONS_MOVE_DOWN => 'lucide-arrow-down',
            FormsIconAlias::COMPONENTS_REPEATER_ACTIONS_MOVE_UP => 'lucide-arrow-up',
            FormsIconAlias::COMPONENTS_REPEATER_ACTIONS_REORDER => 'lucide-grip-vertical',

            // Support & UI Components
            SupportIconAlias::BREADCRUMBS_SEPARATOR => 'lucide-chevron-right',
            SupportIconAlias::MODAL_CLOSE_BUTTON => 'lucide-x',
            SupportIconAlias::SECTION_COLLAPSE_BUTTON => 'lucide-chevron-down',
            SupportIconAlias::PAGINATION_PREVIOUS_BUTTON => 'lucide-chevron-left',
            SupportIconAlias::PAGINATION_NEXT_BUTTON => 'lucide-chevron-right',
            SupportIconAlias::PAGINATION_FIRST_BUTTON => 'lucide-chevrons-left',
            SupportIconAlias::PAGINATION_LAST_BUTTON => 'lucide-chevrons-right',

            // Notifications
            NotificationsIconAlias::DATABASE_MODAL_EMPTY_STATE => 'lucide-bell-off',
            NotificationsIconAlias::NOTIFICATION_CLOSE_BUTTON => 'lucide-x',
            NotificationsIconAlias::NOTIFICATION_DANGER => 'lucide-triangle-alert',
            NotificationsIconAlias::NOTIFICATION_INFO => 'lucide-info',
            NotificationsIconAlias::NOTIFICATION_SUCCESS => 'lucide-circle-check',
            NotificationsIconAlias::NOTIFICATION_WARNING => 'lucide-circle-alert',
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

