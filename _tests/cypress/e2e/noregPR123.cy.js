describe('no reg PR123', () => {
    it('display links and not an error', () => {
        cy.visit('http://localhost/dokuwikiITestsForNsPagesdokuwiki-2020-07-29/doku.php?id=noRegPR123:start&do=edit&rev=0');
        cy.get("#wiki__text")
          .clear()
          .type('<nspages -exclude:page1 -exclude:page10>');
        cy.get("#edbtn__save").click();

        //cy.log("catpagecol object: ", cy.get(".catpagecol"));
        cy.get(".catpagecol").find("a").should('not.be.empty');
        cy.screenshot();
    })

})
