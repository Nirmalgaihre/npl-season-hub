@extends('layouts.app')

@section('title', 'Privacy Policy | NPL Season Hub')
@section('meta_description', 'Read the NPL Season Hub privacy policy and learn how submitted information, cookies, and advertising technologies may be handled.')

@section('content')
<div class="container py-5">
    <div class="mx-auto" style="max-width: 900px;">
        <p class="text-uppercase fw-semibold text-danger mb-2">Your privacy matters</p>
        <h1 class="display-5 fw-bold mb-2">Privacy Policy</h1>
        <p class="text-muted small">Last updated: October 29, 2026</p>

        <p>
            This Privacy Policy explains how NPL Season Hub ("we", "our", or
            "the website") may collect, use, and protect information when you
            visit our website or contact us.
        </p>

        <h2 class="h4 mt-4">1. Information we collect</h2>
        <p>
            If you contact us through our contact form, we may collect your
            name, email address, subject, and message. Our hosting environment
            may also process technical information such as IP address, browser
            type, device information, requested pages, and server logs for
            security, troubleshooting, and website operation.
        </p>

        <h2 class="h4 mt-4">2. How we use information</h2>
        <p>Information may be used to:</p>
        <ul>
            <li>Respond to messages, questions, and correction requests.</li>
            <li>Operate, maintain, and secure the website.</li>
            <li>Diagnose technical problems and prevent abuse.</li>
            <li>Understand website performance and improve visitor experience.</li>
            <li>Meet applicable legal obligations.</li>
        </ul>

        <h2 class="h4 mt-4">3. Cookies and similar technologies</h2>
        <p>
            The website or third-party services may use cookies or similar
            technologies to maintain functionality, understand usage, measure
            performance, or support advertising. You can manage cookies through
            your browser settings. Disabling some cookies may affect website
            functionality.
        </p>

        <h2 class="h4 mt-4">4. Advertising and Google AdSense</h2>
        <p>
            If advertising services such as Google AdSense are enabled, Google
            and its partners may use cookies or similar technologies to serve
            ads, limit repeated ads, measure advertising performance, and
            personalise advertising where permitted by law and your settings.
        </p>
        <p>
            Learn more at
            <a href="https://policies.google.com/technologies/partner-sites"
               target="_blank" rel="noopener noreferrer">
                How Google uses information from sites or apps that use its services
            </a>.
            You can review available Google advertising controls at
            <a href="https://myadcenter.google.com/"
               target="_blank" rel="noopener noreferrer">My Ad Center</a>.
        </p>
        <p>
            This section describes practices that may apply if advertising is
            enabled. We will update this policy to reflect the services actually
            used on this website.
        </p>

        <h2 class="h4 mt-4">5. Sharing of information</h2>
        <p>
            We do not intend to sell personal information submitted through our
            contact form. Information may be processed by hosting, email,
            security, analytics, or advertising providers when those services
            are used, or disclosed when required by law.
        </p>

        <h2 class="h4 mt-4">6. Data retention and security</h2>
        <p>
            We retain messages and technical records only for as long as
            reasonably necessary for their purpose, security, or legal
            obligations. We take reasonable steps to protect information, but
            no internet transmission or storage system can be guaranteed to be
            completely secure.
        </p>

        <h2 class="h4 mt-4">7. External websites</h2>
        <p>
            Our website may link to third-party websites. Their privacy
            practices are governed by their own policies, which we encourage
            you to review.
        </p>

        <h2 class="h4 mt-4">8. Children's privacy</h2>
        <p>
            This website is not intended to knowingly collect personal
            information from children in violation of applicable law. Contact
            us if you believe a child has submitted personal information and
            you would like us to review it.
        </p>

        <h2 class="h4 mt-4">9. Your choices and rights</h2>
        <p>
            Depending on where you live, you may have rights to request access
            to, correction of, or deletion of certain personal information,
            subject to applicable law. Contact us to make a request.
        </p>

        <h2 class="h4 mt-4">10. Changes to this policy</h2>
        <p>
            We may update this policy when our website, services, or legal
            requirements change. Updates will be published on this page with
            a revised date.
        </p>

        <h2 class="h4 mt-4">11. Contact</h2>
        <p>
            For privacy questions, please use our
            <a href="{{ route('contact') }}">Contact Us</a> page.
        </p>
    </div>
</div>
@endsection
