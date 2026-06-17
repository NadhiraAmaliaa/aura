import ApplicationLogo from "@/Components/ApplicationLogo";
import Dropdown from "@/Components/Dropdown";
import FlashMessages from "@/Components/FlashMessages";
import NavLink from "@/Components/NavLink";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink";
import { AuthUser, PageProps } from "@/types";
import { Link, usePage } from "@inertiajs/react";
import { PropsWithChildren, ReactNode, useState } from "react";

interface NavLeaf {
    label: string;
    routeName: string;
    activePattern: string;
}

interface NavGroup {
    label: string;
    items: NavLeaf[];
}

type NavEntry = NavLeaf | NavGroup;

function isGroup(entry: NavEntry): entry is NavGroup {
    return (entry as NavGroup).items !== undefined;
}

const adminNav: NavEntry[] = [
    {
        label: "Dashboard",
        routeName: "admin.dashboard",
        activePattern: "admin.dashboard",
    },
    {
        label: "Operasional",
        items: [
            {
                label: "Peserta Magang",
                routeName: "admin.interns.index",
                activePattern: "admin.interns.*",
            },
            {
                label: "Absensi",
                routeName: "admin.attendances.index",
                activePattern: "admin.attendances.index",
            },
            {
                label: "Pengajuan Izin",
                routeName: "admin.leave-requests.index",
                activePattern: "admin.leave-requests.*",
            },
        ],
    },
    {
        label: "Master Absensi",
        items: [
            {
                label: "Jam Kerja",
                routeName: "admin.working-hours.index",
                activePattern: "admin.working-hours.*",
            },
            {
                label: "Lokasi Absensi",
                routeName: "admin.attendance-locations.index",
                activePattern: "admin.attendance-locations.*",
            },
            {
                label: "Hari Libur",
                routeName: "admin.non-working-days.index",
                activePattern: "admin.non-working-days.*",
            },
        ],
    },
    {
        label: "Reporting",
        items: [
            {
                label: "Reporting Absensi",
                routeName: "admin.attendances.recap",
                activePattern: "admin.attendances.recap",
            },
        ],
    },
    {
        label: "Master Data",
        items: [
            {
                label: "Program Magang",
                routeName: "admin.intern-programs.index",
                activePattern: "admin.intern-programs.*",
            },
            {
                label: "Divisi",
                routeName: "admin.divisions.index",
                activePattern: "admin.divisions.*",
            },
            {
                label: "Perguruan Tinggi",
                routeName: "admin.universities.index",
                activePattern: "admin.universities.*",
            },
            {
                label: "Program Studi",
                routeName: "admin.study-programs.index",
                activePattern: "admin.study-programs.*",
            },
        ],
    },
];

const internNav: NavEntry[] = [
    {
        label: "Dashboard",
        routeName: "intern.dashboard",
        activePattern: "intern.dashboard",
    },
    {
        label: "Absensi",
        routeName: "intern.attendance.index",
        activePattern: "intern.attendance.*",
    },
    {
        label: "Pengajuan Izin",
        routeName: "intern.leave-requests.index",
        activePattern: "intern.leave-requests.*",
    },
];

function NavGroupDropdown({ group }: { group: NavGroup }) {
    const active = group.items.some((child) =>
        route().current(child.activePattern),
    );

    return (
        <Dropdown>
            <Dropdown.Trigger>
                <button
                    type="button"
                    className={
                        "inline-flex h-full items-center border-b-2 px-1 pt-1 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none " +
                        (active
                            ? "border-green-600 text-gray-900"
                            : "border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700")
                    }
                >
                    {group.label}
                    <svg
                        className="-me-0.5 ms-1 h-4 w-4"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fillRule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clipRule="evenodd"
                        />
                    </svg>
                </button>
            </Dropdown.Trigger>

            <Dropdown.Content align="left">
                {group.items.map((child) => (
                    <Dropdown.Link
                        key={child.routeName}
                        href={route(child.routeName)}
                    >
                        {child.label}
                    </Dropdown.Link>
                ))}
            </Dropdown.Content>
        </Dropdown>
    );
}

export default function AuthenticatedLayout({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const user = usePage<PageProps>().props.auth.user as AuthUser;
    const [showingNavigationDropdown, setShowingNavigationDropdown] =
        useState(false);

    const navItems = user.is_admin ? adminNav : internNav;

    return (
        <div className="min-h-screen bg-gray-100">
            <nav className="border-b border-gray-100 bg-white">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex h-16 justify-between">
                        <div className="flex">
                            <div className="flex shrink-0 items-center">
                                <Link
                                    href={route(
                                        user.is_admin
                                            ? "admin.dashboard"
                                            : "intern.dashboard",
                                    )}
                                >
                                    <ApplicationLogo />
                                </Link>
                            </div>

                            <div className="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                {navItems.map((item) =>
                                    isGroup(item) ? (
                                        <NavGroupDropdown
                                            key={item.label}
                                            group={item}
                                        />
                                    ) : (
                                        <NavLink
                                            key={item.routeName}
                                            href={route(item.routeName)}
                                            active={route().current(
                                                item.activePattern,
                                            )}
                                        >
                                            {item.label}
                                        </NavLink>
                                    ),
                                )}
                            </div>
                        </div>

                        <div className="hidden sm:ms-6 sm:flex sm:items-center">
                            <Dropdown>
                                <Dropdown.Trigger>
                                    <span className="inline-flex rounded-md">
                                        <button
                                            type="button"
                                            className="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                        >
                                            {user.name}
                                            <svg
                                                className="-me-0.5 ms-2 h-4 w-4"
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fillRule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clipRule="evenodd"
                                                />
                                            </svg>
                                        </button>
                                    </span>
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

                        <div className="-me-2 flex items-center sm:hidden">
                            <button
                                onClick={() =>
                                    setShowingNavigationDropdown(
                                        (previousState) => !previousState,
                                    )
                                }
                                className="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                            >
                                <svg
                                    className="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        className={
                                            !showingNavigationDropdown
                                                ? "inline-flex"
                                                : "hidden"
                                        }
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        className={
                                            showingNavigationDropdown
                                                ? "inline-flex"
                                                : "hidden"
                                        }
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    className={
                        (showingNavigationDropdown ? "block" : "hidden") +
                        " sm:hidden"
                    }
                >
                    <div className="space-y-1 pb-3 pt-2">
                        {navItems.map((item) =>
                            isGroup(item) ? (
                                <div key={item.label} className="mt-2">
                                    <div className="px-4 py-1 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                        {item.label}
                                    </div>
                                    {item.items.map((child) => (
                                        <ResponsiveNavLink
                                            key={child.routeName}
                                            href={route(child.routeName)}
                                            active={route().current(
                                                child.activePattern,
                                            )}
                                        >
                                            {child.label}
                                        </ResponsiveNavLink>
                                    ))}
                                </div>
                            ) : (
                                <ResponsiveNavLink
                                    key={item.routeName}
                                    href={route(item.routeName)}
                                    active={route().current(item.activePattern)}
                                >
                                    {item.label}
                                </ResponsiveNavLink>
                            ),
                        )}
                    </div>

                    <div className="border-t border-gray-200 pb-1 pt-4">
                        <div className="px-4">
                            <div className="text-base font-medium text-gray-800">
                                {user.name}
                            </div>
                            {user.role === "admin" && user.nik && (
                                <div className="text-sm font-medium text-gray-500">
                                    {user.nik}
                                </div>
                            )}
                        </div>

                        <div className="mt-3 space-y-1">
                            <ResponsiveNavLink href={route("profile.edit")}>
                                Profil
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                method="post"
                                href={route("logout")}
                                as="button"
                            >
                                Keluar
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            {header && (
                <header className="bg-white shadow">
                    <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {header}
                    </div>
                </header>
            )}

            <main className="py-8">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <FlashMessages />
                    {children}
                </div>
            </main>
        </div>
    );
}
