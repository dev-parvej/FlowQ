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
        <?php esc_html_e('Contact Developer', 'flowq'); ?>
    </h1>

    <div class="flowq-contact-intro">
        <p class="description">
            <?php esc_html_e('Need help with FlowQ customization? Want to add custom features or integrate with your workflow? Our development team is here to help!', 'flowq'); ?>
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
                    <span class="dashicons dashicons-megaphone mt-15"></span>
                    <?php esc_html_e('Hire on Upwork', 'flowq'); ?>
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
                    <span class="dashicons dashicons-megaphone mt-15"></span>
                    <?php esc_html_e('Hire on Upwork', 'flowq'); ?>
                </a>
            </div>
        </div>

    </div>

    <!-- Services Section -->
    <div class="flowq-services-section">
        <h2><?php esc_html_e('Custom Development Services', 'flowq'); ?></h2>
        <div class="services-grid">

            <div class="service-card">
                <div class="service-icon">
                    <span class="dashicons dashicons-admin-customizer"></span>
                </div>
                <h3><?php esc_html_e('Plugin Customization', 'flowq'); ?></h3>
                <p><?php esc_html_e('Need custom features for FlowQ? We can add new question types, integrations, or modify existing functionality to match your needs.', 'flowq'); ?></p>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <span class="dashicons dashicons-share-alt2"></span>
                </div>
                <h3><?php esc_html_e('Third-Party Integrations', 'flowq'); ?></h3>
                <p><?php esc_html_e('Connect FlowQ with your favorite tools: CRM systems, email marketing platforms, analytics tools, and more.', 'flowq'); ?></p>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <span class="dashicons dashicons-chart-bar"></span>
                </div>
                <h3><?php esc_html_e('Advanced Analytics', 'flowq'); ?></h3>
                <p><?php esc_html_e('Custom reporting dashboards, data visualization, export formats, and automated insights tailored to your business needs.', 'flowq'); ?></p>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <span class="dashicons dashicons-performance"></span>
                </div>
                <h3><?php esc_html_e('Performance Optimization', 'flowq'); ?></h3>
                <p><?php esc_html_e('Optimize FlowQ for high-traffic sites, improve database queries, and implement caching strategies for faster surveys.', 'flowq'); ?></p>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <span class="dashicons dashicons-art"></span>
                </div>
                <h3><?php esc_html_e('Custom Templates', 'flowq'); ?></h3>
                <p><?php esc_html_e('Create brand-specific templates with custom styling, layouts, and interactive elements that match your brand identity.', 'flowq'); ?></p>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <span class="dashicons dashicons-sos"></span>
                </div>
                <h3><?php esc_html_e('Technical Support', 'flowq'); ?></h3>
                <p><?php esc_html_e('Dedicated support for installation, configuration, troubleshooting, and ongoing maintenance of your FlowQ installation.', 'flowq'); ?></p>
            </div>

        </div>
    </div>

    <!-- Contact CTA -->
    <div class="flowq-contact-cta">
        <div class="cta-content">
            <h2><?php esc_html_e('Ready to Get Started?', 'flowq'); ?></h2>
            <p><?php esc_html_e('Contact us today to discuss your project requirements and get a free quote. We\'re here to help you make the most of FlowQ!', 'flowq'); ?></p>
            <div class="cta-buttons">
                <a href="https://www.upwork.com/freelancers/~01d467394429e6bd08?mp_source=share" target="_blank" rel="noopener noreferrer" class="button button-primary button-hero">
                    <span class="dashicons dashicons-email-alt"></span>
                    <?php esc_html_e('Contact on Upwork', 'flowq'); ?>
                </a>
                <a href="https://github.com/dev-parvej" target="_blank" rel="noopener noreferrer" class="button button-secondary button-hero">
                    <span class="dashicons dashicons-editor-code"></span>
                    <?php esc_html_e('View on GitHub', 'flowq'); ?>
                </a>
            </div>
        </div>
    </div>

</div>

