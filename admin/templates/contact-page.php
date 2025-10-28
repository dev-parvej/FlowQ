<?php
/**
 * Contact / Hire Developer Page Template
 *
 * @package FlowQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap flowq-contact-page">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-groups" style="font-size: 30px; width: 30px; height: 30px;"></span>
        <?php esc_html_e('Contact Developer', 'wp-dynamic-survey'); ?>
    </h1>

    <div class="flowq-contact-intro">
        <p class="description">
            <?php esc_html_e('Need help with FlowQ customization? Want to add custom features or integrate with your workflow? Our development team is here to help!', 'wp-dynamic-survey'); ?>
        </p>
    </div>

    <div class="flowq-contact-grid">

        <!-- Developer Card 1 - Main Developer -->
        <div class="flowq-developer-card">
            <div class="developer-card-header">
                <div class="developer-avatar">
                    <span class="dashicons dashicons-admin-users"></span>
                </div>
                <div class="developer-info">
                    <h2 class="developer-name">Parvej Ahammad</h2>
                    <p class="developer-role">Sr Software Engineer & Plugin Author</p>
                </div>
            </div>

            <div class="developer-card-body">
                <p class="developer-bio">
                    Full-stack Software Engineer with 8+ years of experience building scalable SaaS products using Node.js, React, Laravel, WordPress, and microservices. Proven leadership in team-based and cross-functional environments with a focus on performance, clean code, and reliability.
                </p>

                <div class="developer-skills">
                    <div class="skill-category">
                        <h4 class="skill-category-title">Languages & Frameworks</h4>
                        <div class="skill-badges">
                            <span class="skill-badge">PHP</span>
                            <span class="skill-badge">Laravel</span>
                            <span class="skill-badge">WordPress</span>
                            <span class="skill-badge">JavaScript</span>
                            <span class="skill-badge">TypeScript</span>
                            <span class="skill-badge">Node.js</span>
                            <span class="skill-badge">NestJS</span>
                        </div>
                    </div>
                    <div class="skill-category">
                        <h4 class="skill-category-title">Frontend</h4>
                        <div class="skill-badges">
                            <span class="skill-badge">React</span>
                            <span class="skill-badge">Next.js</span>
                            <span class="skill-badge">Vue</span>
                            <span class="skill-badge">Nuxt.js</span>
                            <span class="skill-badge">jQuery</span>
                        </div>
                    </div>
                    <div class="skill-category">
                        <h4 class="skill-category-title">Database</h4>
                        <div class="skill-badges">
                            <span class="skill-badge">MySQL</span>
                            <span class="skill-badge">PostgreSQL</span>
                            <span class="skill-badge">MongoDB</span>
                            <span class="skill-badge">Redis</span>
                        </div>
                    </div>
                    <div class="skill-category">
                        <h4 class="skill-category-title">Tools & DevOps</h4>
                        <div class="skill-badges">
                            <span class="skill-badge">Git</span>
                            <span class="skill-badge">Docker</span>
                            <span class="skill-badge">AWS (Basic)</span>
                            <span class="skill-badge">Microservices</span>
                            <span class="skill-badge">CI/CD</span>
                        </div>
                    </div>
                </div>

                <div class="developer-social">
                    <a href="https://www.upwork.com/freelancers/~01d467394429e6bd08?mp_source=share" target="_blank" rel="noopener noreferrer" class="social-link upwork">
                        <span class="dashicons dashicons-money-alt"></span>
                        <span>Upwork Profile</span>
                    </a>
                    <a href="https://www.linkedin.com/in/dev-parvej" target="_blank" rel="noopener noreferrer" class="social-link linkedin">
                        <span class="dashicons dashicons-linkedin"></span>
                        <span>LinkedIn</span>
                    </a>
                    <a href="https://github.com/dev-parvej" target="_blank" rel="noopener noreferrer" class="social-link github">
                        <span class="dashicons dashicons-editor-code"></span>
                        <span>GitHub</span>
                    </a>
                </div>
            </div>

            <div class="developer-card-footer">
                <a href="https://www.upwork.com/freelancers/~01d467394429e6bd08?mp_source=share" target="_blank" rel="noopener noreferrer" class="button button-primary button-hero hire-button">
                    <span class="dashicons dashicons-megaphone"></span>
                    <?php esc_html_e('Hire on Upwork', 'wp-dynamic-survey'); ?>
                </a>
            </div>
        </div>

        <!-- Developer Card 2 - Work Partner -->
        <div class="flowq-developer-card">
            <div class="developer-card-header">
                <div class="developer-avatar partner">
                    <span class="dashicons dashicons-admin-users"></span>
                </div>
                <div class="developer-info">
                    <h2 class="developer-name">Fazlur Rahman</h2>
                    <p class="developer-role">Sr Software Engineer</p>
                </div>
            </div>

            <div class="developer-card-body">
                <p class="developer-bio">
                    Experienced WordPress developer with expertise in theme development,
                    WooCommerce customization, and performance optimization. Passionate about
                    creating user-friendly solutions.
                </p>

                <div class="developer-skills">
                    <div class="skill-category">
                        <h4 class="skill-category-title">Languages & Frameworks</h4>
                        <div class="skill-badges">
                            <span class="skill-badge">PHP</span>
                            <span class="skill-badge">Laravel</span>
                            <span class="skill-badge">WordPress</span>
                            <span class="skill-badge">JavaScript</span>
                            <span class="skill-badge">TypeScript</span>
                            <span class="skill-badge">Node.js</span>
                            <span class="skill-badge">NestJS</span>
                        </div>
                    </div>
                    <div class="skill-category">
                        <h4 class="skill-category-title">Frontend</h4>
                        <div class="skill-badges">
                            <span class="skill-badge">React</span>
                            <span class="skill-badge">Next.js</span>
                            <span class="skill-badge">Vue</span>
                            <span class="skill-badge">Nuxt.js</span>
                            <span class="skill-badge">jQuery</span>
                        </div>
                    </div>
                    <div class="skill-category">
                        <h4 class="skill-category-title">Database</h4>
                        <div class="skill-badges">
                            <span class="skill-badge">MySQL</span>
                            <span class="skill-badge">PostgreSQL</span>
                            <span class="skill-badge">MongoDB</span>
                            <span class="skill-badge">Redis</span>
                        </div>
                    </div>
                    <div class="skill-category">
                        <h4 class="skill-category-title">Tools & DevOps</h4>
                        <div class="skill-badges">
                            <span class="skill-badge">Git</span>
                            <span class="skill-badge">Docker</span>
                            <span class="skill-badge">AWS (Basic)</span>
                            <span class="skill-badge">Microservices</span>
                            <span class="skill-badge">CI/CD</span>
                        </div>
                    </div>
                </div>

                <div class="developer-social">
                    <a href="https://www.upwork.com/freelancers/~01ac56205d248d7f5d?mp_source=share" target="_blank" rel="noopener noreferrer" class="social-link upwork">
                        <span class="dashicons dashicons-money-alt"></span>
                        <span>Upwork Profile</span>
                    </a>
                </div>
            </div>

            <div class="developer-card-footer">
                <a href="https://www.upwork.com/freelancers/~01ac56205d248d7f5d?mp_source=share" target="_blank" rel="noopener noreferrer" class="button button-primary button-hero hire-button">
                    <span class="dashicons dashicons-megaphone"></span>
                    <?php esc_html_e('Hire on Upwork', 'wp-dynamic-survey'); ?>
                </a>
            </div>
        </div>

    </div>

    <!-- Services Section -->
    <div class="flowq-services-section">
        <h2><?php esc_html_e('Custom Development Services', 'wp-dynamic-survey'); ?></h2>
        <div class="services-grid">

            <div class="service-card">
                <div class="service-icon">
                    <span class="dashicons dashicons-admin-customizer"></span>
                </div>
                <h3><?php esc_html_e('Plugin Customization', 'wp-dynamic-survey'); ?></h3>
                <p><?php esc_html_e('Need custom features for FlowQ? We can add new question types, integrations, or modify existing functionality to match your needs.', 'wp-dynamic-survey'); ?></p>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <span class="dashicons dashicons-share-alt2"></span>
                </div>
                <h3><?php esc_html_e('Third-Party Integrations', 'wp-dynamic-survey'); ?></h3>
                <p><?php esc_html_e('Connect FlowQ with your favorite tools: CRM systems, email marketing platforms, analytics tools, and more.', 'wp-dynamic-survey'); ?></p>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <span class="dashicons dashicons-chart-bar"></span>
                </div>
                <h3><?php esc_html_e('Advanced Analytics', 'wp-dynamic-survey'); ?></h3>
                <p><?php esc_html_e('Custom reporting dashboards, data visualization, export formats, and automated insights tailored to your business needs.', 'wp-dynamic-survey'); ?></p>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <span class="dashicons dashicons-performance"></span>
                </div>
                <h3><?php esc_html_e('Performance Optimization', 'wp-dynamic-survey'); ?></h3>
                <p><?php esc_html_e('Optimize FlowQ for high-traffic sites, improve database queries, and implement caching strategies for faster surveys.', 'wp-dynamic-survey'); ?></p>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <span class="dashicons dashicons-art"></span>
                </div>
                <h3><?php esc_html_e('Custom Templates', 'wp-dynamic-survey'); ?></h3>
                <p><?php esc_html_e('Create brand-specific templates with custom styling, layouts, and interactive elements that match your brand identity.', 'wp-dynamic-survey'); ?></p>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <span class="dashicons dashicons-sos"></span>
                </div>
                <h3><?php esc_html_e('Technical Support', 'wp-dynamic-survey'); ?></h3>
                <p><?php esc_html_e('Dedicated support for installation, configuration, troubleshooting, and ongoing maintenance of your FlowQ installation.', 'wp-dynamic-survey'); ?></p>
            </div>

        </div>
    </div>

    <!-- Contact CTA -->
    <div class="flowq-contact-cta">
        <div class="cta-content">
            <h2><?php esc_html_e('Ready to Get Started?', 'wp-dynamic-survey'); ?></h2>
            <p><?php esc_html_e('Contact us today to discuss your project requirements and get a free quote. We\'re here to help you make the most of FlowQ!', 'wp-dynamic-survey'); ?></p>
            <div class="cta-buttons">
                <a href="https://www.upwork.com/freelancers/~01d467394429e6bd08?mp_source=share" target="_blank" rel="noopener noreferrer" class="button button-primary button-hero">
                    <span class="dashicons dashicons-email-alt"></span>
                    <?php esc_html_e('Contact on Upwork', 'wp-dynamic-survey'); ?>
                </a>
                <a href="https://github.com/dev-parvej" target="_blank" rel="noopener noreferrer" class="button button-secondary button-hero">
                    <span class="dashicons dashicons-editor-code"></span>
                    <?php esc_html_e('View on GitHub', 'wp-dynamic-survey'); ?>
                </a>
            </div>
        </div>
    </div>

</div>

<style>
/* Contact Page Styles */
.flowq-contact-page {
    max-width: 1400px;
    margin: 20px 0;
}

.flowq-contact-intro {
    background: #fff;
    border-left: 4px solid #2271b1;
    padding: 15px 20px;
    margin: 20px 0;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.flowq-contact-intro .description {
    font-size: 14px;
    line-height: 1.6;
    margin: 0;
    color: #50575e;
}

/* Developer Cards Grid */
.flowq-contact-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 30px;
    margin: 30px 0;
}

@media (max-width: 782px) {
    .flowq-contact-grid {
        grid-template-columns: 1fr;
    }
}

.flowq-developer-card {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
    transition: all 0.3s ease;
}

.flowq-developer-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,.12);
    transform: translateY(-2px);
}

.developer-card-header {
    background: linear-gradient(135deg, #2271b1 0%, #135e96 100%);
    padding: 30px 25px;
    display: flex;
    align-items: center;
    gap: 20px;
}

.developer-avatar {
    width: 80px;
    height: 80px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid rgba(255,255,255,0.3);
}

.developer-avatar.partner {
    background: rgba(255,255,255,0.25);
}

.developer-avatar .dashicons {
    font-size: 40px;
    width: 40px;
    height: 40px;
    color: #fff;
}

.developer-info {
    flex: 1;
}

.developer-name {
    color: #fff;
    margin: 0 0 5px 0;
    font-size: 24px;
    font-weight: 600;
}

.developer-role {
    color: rgba(255,255,255,0.9);
    margin: 0;
    font-size: 14px;
}

.developer-card-body {
    padding: 25px;
}

.developer-bio {
    font-size: 14px;
    line-height: 1.6;
    color: #50575e;
    margin: 0 0 20px 0;
}

.developer-skills {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 20px;
}

.skill-category {
    margin-bottom: 8px;
}

.skill-category-title {
    font-size: 13px;
    font-weight: 600;
    color: #1d2327;
    margin: 0 0 8px 0;
    padding-bottom: 4px;
    border-bottom: 1px solid #e5e5e5;
}

.skill-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.skill-badge {
    display: inline-block;
    padding: 6px 12px;
    background: #f0f6fc;
    color: #2271b1;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    border: 1px solid #d0e3f0;
}

.developer-social {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.social-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 15px;
    background: #f6f7f7;
    border-radius: 6px;
    text-decoration: none;
    color: #2c3338;
    transition: all 0.2s ease;
    border: 1px solid #dcdcde;
}

.social-link:hover {
    background: #fff;
    border-color: #2271b1;
    color: #2271b1;
    transform: translateX(5px);
}

.social-link .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}

.social-link.upwork .dashicons {
    color: #14a800;
}

.social-link.linkedin .dashicons {
    color: #0077b5;
}

.social-link.github .dashicons {
    color: #333;
}

.developer-card-footer {
    padding: 0 25px 25px 25px;
}

.hire-button {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    font-size: 14px !important;
    padding: 12px 20px !important;
    height: auto !important;
}

.hire-button .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}

/* Services Section */
.flowq-services-section {
    background: #fff;
    padding: 40px 30px;
    border-radius: 8px;
    margin: 30px 0;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
}

.flowq-services-section h2 {
    text-align: center;
    margin: 0 0 30px 0;
    font-size: 28px;
    color: #1d2327;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
}

@media (max-width: 782px) {
    .services-grid {
        grid-template-columns: 1fr;
    }
}

.service-card {
    padding: 25px;
    background: #f9f9f9;
    border-radius: 8px;
    border: 1px solid #e5e5e5;
    transition: all 0.3s ease;
}

.service-card:hover {
    background: #fff;
    border-color: #2271b1;
    box-shadow: 0 2px 8px rgba(34, 113, 177, 0.1);
}

.service-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #2271b1 0%, #135e96 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
}

.service-icon .dashicons {
    font-size: 26px;
    width: 26px;
    height: 26px;
    color: #fff;
}

.service-card h3 {
    margin: 0 0 10px 0;
    font-size: 18px;
    color: #1d2327;
}

.service-card p {
    margin: 0;
    font-size: 14px;
    line-height: 1.6;
    color: #50575e;
}

/* Contact CTA */
.flowq-contact-cta {
    background: linear-gradient(135deg, #2271b1 0%, #135e96 100%);
    padding: 50px 40px;
    border-radius: 8px;
    text-align: center;
    margin: 30px 0;
    box-shadow: 0 4px 16px rgba(0,0,0,.15);
}

.cta-content h2 {
    color: #fff;
    margin: 0 0 15px 0;
    font-size: 32px;
}

.cta-content p {
    color: rgba(255,255,255,0.95);
    font-size: 16px;
    line-height: 1.6;
    margin: 0 0 25px 0;
    max-width: 700px;
    margin-left: auto;
    margin-right: auto;
}

.cta-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
}

.cta-buttons .button {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px !important;
    padding: 12px 24px !important;
    height: auto !important;
}

.cta-buttons .button .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}

.cta-buttons .button-secondary {
    background: rgba(255,255,255,0.2);
    border-color: rgba(255,255,255,0.3);
    color: #fff;
}

.cta-buttons .button-secondary:hover {
    background: rgba(255,255,255,0.3);
    border-color: rgba(255,255,255,0.5);
}
</style>
