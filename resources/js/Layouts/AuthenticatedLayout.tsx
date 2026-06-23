import Dropdown from "@/Components/Dropdown";
import FlashToaster from "@/Components/FlashToaster";
import MaterialIcon from "@/Components/MaterialIcon";
import { AuthUser, PageProps } from "@/types";
import { Link, usePage } from "@inertiajs/react";
import {
    PropsWithChildren,
    ReactNode,
    useCallback,
    useRef,
    useState,
} from "react";

const SIDEBAR_SCROLL_KEY = "admin-sidebar-scroll";

interface NavLeaf {
    label: string;
    icon: string;
    routeName: string;
    activePattern: string;
}

interface NavGroup {
    label: string;
    icon: string;
    items: NavLeaf[];
}

type NavEntry = NavLeaf | NavGroup;

function isGroup(entry: NavEntry): entry is NavGroup {
    return (entry as NavGroup).items !== undefined;
}

const adminNav: NavEntry[] = [
    {
        label: "Dashboard",
        icon: "dashboard",
        routeName: "admin.dashboard",
        activePattern: "admin.dashboard",
    },
    {
        label: "Manajemen User",
        icon: "manage_accounts",
        routeName: "admin.users.index",
        activePattern: "admin.users.*",
    },
    {
        label: "Operasional",
        icon: "work",
        items: [
            {
                label: "Peserta Magang",
                icon: "groups",
                routeName: "admin.interns.index",
                activePattern: "admin.interns.*",
            },
            {
                label: "Pengajuan Izin",
                icon: "event_available",
                routeName: "admin.leave-requests.index",
                activePattern: "admin.leave-requests.*",
            },
        ],
    },
    {
        label: "Master Absensi",
        icon: "calendar_month",
        items: [
            {
                label: "Jam Kerja",
                icon: "schedule",
                routeName: "admin.working-hours.index",
                activePattern: "admin.working-hours.*",
            },
            {
                label: "Lokasi Absensi",
                icon: "location_on",
                routeName: "admin.attendance-locations.index",
                activePattern: "admin.attendance-locations.*",
            },
            {
                label: "Hari Libur",
                icon: "event_busy",
                routeName: "admin.non-working-days.index",
                activePattern: "admin.non-working-days.*",
            },
        ],
    },
    {
        label: "Reporting",
        icon: "assessment",
        items: [
            {
                label: "Reporting Absensi",
                icon: "summarize",
                routeName: "admin.attendances.index",
                activePattern: "admin.attendances.*",
            },
        ],
    },
    {
        label: "Master Data",
        icon: "database",
        items: [
            {
                label: "Program Magang",
                icon: "school",
                routeName: "admin.intern-programs.index",
                activePattern: "admin.intern-programs.*",
            },
            {
                label: "Divisi",
                icon: "apartment",
                routeName: "admin.divisions.index",
                activePattern: "admin.divisions.*",
            },
            {
                label: "Perguruan Tinggi",
                icon: "account_balance",
                routeName: "admin.universities.index",
                activePattern: "admin.universities.*",
            },
            {
                label: "Program Studi",
                icon: "menu_book",
                routeName: "admin.study-programs.index",
                activePattern: "admin.study-programs.*",
            },
        ],
    },
];

const internNav: NavEntry[] = [
    {
        label: "Dashboard",
        icon: "dashboard",
        routeName: "intern.dashboard",
        activePattern: "intern.dashboard",
    },
    {
        label: "Absensi",
        icon: "fingerprint",
        routeName: "intern.attendance.index",
        activePattern: "intern.attendance.*",
    },
    {
        label: "Pengajuan Izin",
        icon: "event_available",
        routeName: "intern.leave-requests.index",
        activePattern: "intern.leave-requests.*",
    },
];

// Supervisors reuse the administrator pages but only see a subset of the
// navigation; their data is scoped to their division by the backend.
const supervisorNav: NavEntry[] = [
    {
        label: "Dashboard",
        icon: "dashboard",
        routeName: "admin.dashboard",
        activePattern: "admin.dashboard",
    },
    {
        label: "Operasional",
        icon: "work",
        items: [
            {
                label: "Peserta Magang",
                icon: "groups",
                routeName: "admin.interns.index",
                activePattern: "admin.interns.*",
            },
            {
                label: "Pengajuan Izin",
                icon: "event_available",
                routeName: "admin.leave-requests.index",
                activePattern: "admin.leave-requests.*",
            },
        ],
    },
    {
        label: "Reporting",
        icon: "assessment",
        items: [
            {
                label: "Reporting Absensi",
                icon: "summarize",
                routeName: "admin.attendances.index",
                activePattern: "admin.attendances.*",
            },
        ],
    },
];

function initials(name: string): string {
    return name
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() ?? "")
        .join("");
}

function SidebarLink({
    icon,
    label,
    href,
    active,
}: {
    icon: string;
    label: string;
    href: string;
    active: boolean;
}) {
    return (
        <Link
            href={href}
            className={
                "flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200 " +
                (active
                    ? "border-l-4 border-primary bg-surface-container-high text-primary"
                    : "border-l-4 border-transparent text-on-surface-variant hover:translate-x-1 hover:bg-surface-container-low")
            }
        >
            <MaterialIcon name={icon} filled={active} />
            <span>{label}</span>
        </Link>
    );
}

export default function AuthenticatedLayout({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const user = usePage<PageProps>().props.auth.user as AuthUser;
    const [sidebarOpen, setSidebarOpen] = useState(false);

    // Preserve the sidebar scroll position across Inertia navigations so it
    // doesn't jump back to the top when a menu item is clicked.
    const setSidebarRef = useCallback((node: HTMLElement | null) => {
        if (!node) {
            return;
        }

        const stored = sessionStorage.getItem(SIDEBAR_SCROLL_KEY);
        if (stored) {
            node.scrollTop = Number(stored);
        }
    }, []);

    const rememberSidebarScroll = (event: React.UIEvent<HTMLElement>) => {
        sessionStorage.setItem(
            SIDEBAR_SCROLL_KEY,
            String(event.currentTarget.scrollTop),
        );
    };

    const navItems = user.is_admin
        ? adminNav
        : user.is_supervisor
          ? supervisorNav
          : internNav;
    const homeRoute = route(
        user.is_intern ? "intern.dashboard" : "admin.dashboard",
    );

    return (
        <div className="min-h-screen bg-slate-100 text-on-surface">
            {/* Top navigation bar */}
            <header className="fixed left-0 top-0 z-50 flex h-20 w-full items-center justify-between border-b border-outline-variant bg-primary px-4 shadow-sm sm:px-8">
                <div className="flex items-center gap-4 sm:gap-8">
                    <button
                        type="button"
                        onClick={() => setSidebarOpen((open) => !open)}
                        className="rounded-lg p-1 text-white/90 transition-colors hover:bg-white/10 md:hidden"
                        aria-label="Buka menu"
                    >
                        <MaterialIcon name="menu" />
                    </button>

                    <Link
                        href={homeRoute}
                        className="text-2xl font-extrabold tracking-tight text-white"
                    >
                        aghris
                    </Link>
                </div>

                <div className="flex items-center gap-4">
                    <div className="hidden items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 sm:flex">
                        <MaterialIcon
                            name="notifications"
                            filled
                            className="text-white"
                        />
                        <div className="h-4 w-px bg-white/20" />
                        <MaterialIcon name="help" className="text-white" />
                    </div>

                    <Dropdown>
                        <Dropdown.Trigger>
                            <button
                                type="button"
                                className="flex items-center gap-3 pl-2 active:opacity-80"
                            >
                                <div className="hidden text-right sm:block">
                                    <p className="text-xs font-bold uppercase tracking-wide text-white">
                                        {user.name}
                                    </p>
                                    <p className="text-[10px] uppercase tracking-wider text-white/80">
                                        {user.is_admin
                                            ? "Administrator"
                                            : user.is_supervisor
                                              ? "Supervisor"
                                              : "Peserta Magang"}
                                    </p>
                                </div>
                                <div className="flex h-10 w-10 items-center justify-center rounded-full border-2 border-white/30 bg-white/15 text-sm font-bold text-white">
                                    {initials(user.name)}
                                </div>
                            </button>
                        </Dropdown.Trigger>

                        <Dropdown.Content>
                            <Dropdown.Link href={route("profile.edit")}>
                                Profil
                            </Dropdown.Link>
                            <Dropdown.Link
                                href={route("logout")}
                                method="post"
                                as="button"
                            >
                                Keluar
                            </Dropdown.Link>
                        </Dropdown.Content>
                    </Dropdown>
                </div>
            </header>

            {/* Mobile sidebar overlay */}
            {sidebarOpen && (
                <button
                    type="button"
                    aria-label="Tutup menu"
                    onClick={() => setSidebarOpen(false)}
                    className="fixed inset-0 top-20 z-30 bg-black/30 md:hidden"
                />
            )}

            {/* Sidebar */}
            <aside
                className={
                    "fixed left-0 top-20 z-40 flex h-[calc(100vh-5rem)] w-56 flex-col overflow-y-auto border-r border-outline-variant bg-white py-4 transition-transform duration-200 md:translate-x-0 " +
                    (sidebarOpen ? "translate-x-0" : "-translate-x-full")
                }
                ref={setSidebarRef}
                onScroll={rememberSidebarScroll}
            >
                <div className="mb-8 px-4">
                    <div className="flex items-center gap-2 rounded-xl bg-primary-container/10 p-2">
                        <div className="flex h-8 w-8 items-center justify-center rounded bg-primary-container text-white">
                            <MaterialIcon
                                name="corporate_fare"
                                style={{ fontSize: "20px" }}
                            />
                        </div>
                        <div>
                            <p className="text-sm font-bold leading-tight text-primary">
                                Enterprise HR
                            </p>
                            <p className="text-[10px] text-on-surface-variant">
                                Attendance System
                            </p>
                        </div>
                    </div>
                </div>

                <nav
                    className="flex-1 space-y-1"
                    onClick={() => setSidebarOpen(false)}
                >
                    {navItems.map((entry) =>
                        isGroup(entry) ? (
                            <div key={entry.label} className="pt-2">
                                <p className="px-4 pb-1 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant/70">
                                    {entry.label}
                                </p>
                                {entry.items.map((child) => (
                                    <SidebarLink
                                        key={child.routeName}
                                        icon={child.icon}
                                        label={child.label}
                                        href={route(child.routeName)}
                                        active={route().current(
                                            child.activePattern,
                                        )}
                                    />
                                ))}
                            </div>
                        ) : (
                            <SidebarLink
                                key={entry.routeName}
                                icon={entry.icon}
                                label={entry.label}
                                href={route(entry.routeName)}
                                active={route().current(entry.activePattern)}
                            />
                        ),
                    )}
                </nav>

                <div className="mt-auto border-t border-outline-variant px-4 pt-6">
                    <Link
                        href={route("logout")}
                        method="post"
                        as="button"
                        className="mb-2 flex w-full items-center justify-center gap-2 rounded bg-error py-2.5 text-sm font-semibold text-white transition-colors hover:brightness-110"
                    >
                        <MaterialIcon
                            name="logout"
                            style={{ fontSize: "18px" }}
                        />
                        Logout
                    </Link>
                </div>
            </aside>

            <FlashToaster />

            {/* Main content */}
            <main className="min-h-screen p-4 pt-24 sm:p-8 sm:pt-24 md:ml-56">
                {header && (
                    <div className="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
                        {header}
                    </div>
                )}
                {children}
            </main>
        </div>
    );
}
