import MaterialIcon from "@/Components/MaterialIcon";
import { Link } from "@inertiajs/react";
import { PropsWithChildren } from "react";

export default function GuestLayout({ children }: PropsWithChildren) {
    return (
        <div className="flex min-h-screen bg-slate-100">
            {/* Left Hero Section - Hidden on mobile */}
            <div className="hidden w-1/2 bg-gradient-to-br from-blue-900 via-blue-700 to-blue-500 p-12 text-white lg:flex lg:flex-col lg:justify-between">
                <div>
                    <div className="mb-2 inline-flex items-center gap-2 rounded-xl bg-white/10 px-3 py-1.5 backdrop-blur-sm">
                        <MaterialIcon
                            name="verified_user"
                            style={{ fontSize: 18 }}
                        />
                        <span className="text-sm font-semibold">
                            Enterprise HR System
                        </span>
                    </div>
                    <h1 className="mt-6 text-5xl font-bold leading-tight">
                        Welcome to
                        <br />
                        Aura
                    </h1>
                    <p className="mt-4 text-lg text-white/80">
                        Comprehensive attendance management system for
                        internship programs
                    </p>
                </div>

                <div className="space-y-4">
                    <div className="flex items-start gap-3">
                        <MaterialIcon
                            name="check_circle"
                            style={{ fontSize: 24 }}
                            className="mt-1 flex-shrink-0"
                        />
                        <div>
                            <h3 className="font-semibold">
                                Real-time Monitoring
                            </h3>
                            <p className="text-sm text-white/70">
                                Track attendance across all interns
                            </p>
                        </div>
                    </div>
                    <div className="flex items-start gap-3">
                        <MaterialIcon
                            name="check_circle"
                            style={{ fontSize: 24 }}
                            className="mt-1 flex-shrink-0"
                        />
                        <div>
                            <h3 className="font-semibold">Leave Management</h3>
                            <p className="text-sm text-white/70">
                                Streamlined approval workflows
                            </p>
                        </div>
                    </div>
                    <div className="flex items-start gap-3">
                        <MaterialIcon
                            name="check_circle"
                            style={{ fontSize: 24 }}
                            className="mt-1 flex-shrink-0"
                        />
                        <div>
                            <h3 className="font-semibold">Advanced Reports</h3>
                            <p className="text-sm text-white/70">
                                Detailed analytics and insights
                            </p>
                        </div>
                    </div>
                </div>

                <p className="text-sm text-white/60">
                    © 2024 PTPN Internship Program. All rights reserved.
                </p>
            </div>

            {/* Right Login Section */}
            <div className="flex w-full flex-col items-center justify-center px-4 lg:w-1/2">
                <div className="w-full max-w-md">
                    {/* Logo - shown on mobile */}
                    <div className="mb-8 flex items-center justify-center lg:hidden">
                        <Link
                            href="/"
                            className="flex flex-col items-center gap-2"
                        >
                            <div className="flex h-14 w-14 items-center justify-center rounded-lg bg-gradient-to-br from-blue-600 to-blue-400">
                                <MaterialIcon
                                    name="dashboard"
                                    style={{ fontSize: 28, color: "white" }}
                                />
                            </div>
                            <div className="text-center">
                                <div className="text-xl font-bold text-gray-800">
                                    Aura
                                </div>
                                <div className="text-xs text-gray-500">
                                    Enterprise HR System
                                </div>
                            </div>
                        </Link>
                    </div>

                    {children}
                </div>
            </div>
        </div>
    );
}
