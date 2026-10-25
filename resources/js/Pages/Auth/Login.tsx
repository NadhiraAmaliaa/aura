import "../../../css/login.css";
import { Head, useForm } from "@inertiajs/react";
import { FormEventHandler, useState } from "react";

// Existing project background, kept as-is.
const HERO_IMAGE =
    "https://images.unsplash.com/photo-1711192702535-eac61a78ecb0?q=80&w=1153&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D";

const ASSET = "/images/login";

export default function Login({ status }: { status?: string }) {
    const [showPassword, setShowPassword] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        login_as: "admin",
        nik: "",
        password: "",
        remember: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route("login"), {
            onFinish: () => reset("password"),
        });
    };

    return (
        <>
            <Head title="Login" />

            <div className="aura-login">
                <div className="aura-login__card">
                    <div className="aura-login__stage">
                        {/* Decorative layers (desktop only) */}
                        <img
                            alt=""
                            src={`${ASSET}/leaf-top.png`}
                            className="aura-login__decor aura-login__leaf-top"
                        />
                        <img
                            alt=""
                            src={`${ASSET}/leaf-right.png`}
                            className="aura-login__decor aura-login__leaf-right"
                        />

                        <div className="aura-login__hero" aria-hidden="true">
                            <img
                                alt=""
                                src={HERO_IMAGE}
                                className="aura-login__photo"
                            />
                            <div className="aura-login__veil" />
                        </div>

                        <img
                            alt=""
                            src={`${ASSET}/leaf-divider.png`}
                            className="aura-login__decor aura-login__leaf-divider"
                        />

                        <div className="aura-login__hero">
                            <div className="aura-login__brand">
                                <img
                                    alt="PTPN I"
                                    src="/images/logos/logo-ptpn.png"
                                />
                                <span>ptpn1</span>
                            </div>

                            <p className="aura-login__eyebrow">
                                ABSENSI &amp; PERIZINAN MAGANG
                            </p>

                            <h2 className="aura-login__headline">
                                Bertumbuh
                                <br />
                                Bersama
                                <br />
                                Untuk Negeri
                            </h2>

                            <svg
                                className="aura-login__headline-leaf"
                                viewBox="0 0 54 86"
                                aria-hidden="true"
                            >
                                <path
                                    d="M14 4C26 16 28 34 20 46C10 38 8 18 14 4Z"
                                    fill="#e3b24a"
                                />
                                <path
                                    d="M52 44C46 70 28 84 4 82C6 58 26 44 52 44Z"
                                    fill="#6a8a48"
                                />
                            </svg>

                            <div className="aura-login__rule" />

                            <div className="aura-login__values">
                                <span>AKTIF</span>
                                <i>|</i>
                                <span>DISIPLIN</span>
                                <i>|</i>
                                <span>BERDAMPAK</span>
                            </div>
                        </div>

                        {/* Login form */}
                        <section className="aura-login__panel">
                            <img
                                alt="Aura"
                                src={`${ASSET}/aura-logo.png`}
                                className="aura-login__logo"
                            />
                            <p className="aura-login__tagline">
                                Sistem Absensi &amp; Perizinan Magang
                            </p>

                            {status && (
                                <div className="aura-login__status">
                                    {status}
                                </div>
                            )}

                            <form onSubmit={submit} noValidate>
                                <div className="aura-login__field">
                                    <svg
                                        className="aura-login__field-icon"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        strokeWidth="1.6"
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        aria-hidden="true"
                                    >
                                        <circle cx="12" cy="8" r="4" />
                                        <path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7" />
                                    </svg>
                                    <input
                                        id="nik"
                                        type="text"
                                        name="nik"
                                        value={data.nik}
                                        className="aura-login__input"
                                        placeholder="Nomor Identitas"
                                        aria-label="Nomor Identitas"
                                        autoComplete="username"
                                        autoFocus
                                        onChange={(e) =>
                                            setData("nik", e.target.value)
                                        }
                                    />
                                    {errors.nik && (
                                        <p className="aura-login__error">
                                            {errors.nik}
                                        </p>
                                    )}
                                </div>

                                <div className="aura-login__field">
                                    <svg
                                        className="aura-login__field-icon"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        strokeWidth="1.6"
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        aria-hidden="true"
                                    >
                                        <rect
                                            x="4"
                                            y="10.5"
                                            width="16"
                                            height="10.5"
                                            rx="2.5"
                                        />
                                        <path d="M8 10.5V8a4 4 0 0 1 8 0v2.5" />
                                        <circle cx="12" cy="15.5" r="1" />
                                    </svg>
                                    <input
                                        id="password"
                                        type={
                                            showPassword ? "text" : "password"
                                        }
                                        name="password"
                                        value={data.password}
                                        className="aura-login__input"
                                        placeholder="Password"
                                        aria-label="Password"
                                        autoComplete="current-password"
                                        onChange={(e) =>
                                            setData("password", e.target.value)
                                        }
                                    />
                                    <button
                                        type="button"
                                        className="aura-login__eye"
                                        aria-label={
                                            showPassword
                                                ? "Sembunyikan password"
                                                : "Tampilkan password"
                                        }
                                        onClick={() =>
                                            setShowPassword((v) => !v)
                                        }
                                    >
                                        <svg
                                            viewBox="0 0 24 24"
                                            width="100%"
                                            height="100%"
                                            fill="none"
                                            stroke="currentColor"
                                            strokeWidth="1.6"
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            aria-hidden="true"
                                        >
                                            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z" />
                                            <circle cx="12" cy="12" r="3" />
                                            {showPassword && (
                                                <path d="M4 4l16 16" />
                                            )}
                                        </svg>
                                    </button>
                                    {errors.password && (
                                        <p className="aura-login__error">
                                            {errors.password}
                                        </p>
                                    )}
                                </div>

                                <div className="aura-login__row">
                                    <label className="aura-login__remember">
                                        <input
                                            type="checkbox"
                                            name="remember"
                                            checked={data.remember}
                                            onChange={(e) =>
                                                setData(
                                                    "remember",
                                                    e.target.checked,
                                                )
                                            }
                                        />
                                        <span className="aura-login__box">
                                            <svg
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                strokeWidth="3.2"
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                aria-hidden="true"
                                            >
                                                <path d="M5 12.5l4.5 4.5L19 7.5" />
                                            </svg>
                                        </span>
                                        Ingat saya
                                    </label>
                                    <a href="#" className="aura-login__link">
                                        Lupa password?
                                    </a>
                                </div>

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="aura-login__submit"
                                >
                                    {processing ? "MEMPROSES..." : "MASUK"}
                                    {!processing && (
                                        <svg
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            strokeWidth="1.8"
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            aria-hidden="true"
                                        >
                                            <path d="M4 12h16M14 6l6 6-6 6" />
                                        </svg>
                                    )}
                                </button>
                            </form>

                            <p className="aura-login__footer">
                                <span>
                                    Belum memiliki akun?{" "}
                                    <a href="#">Hubungi Admin</a>
                                </span>
                            </p>
                        </section>
                    </div>
                </div>
            </div>
        </>
    );
}
